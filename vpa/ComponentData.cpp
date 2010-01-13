
#include <QRegExp>
#include <QStringList>
#include <QThread>
#include <QTime>

#include "ComponentDataRoot.h"
#include "Generator.h"
#include "ComponentData.h"
#include "ConnectionThread.h"

#define ifDebugCreateComponentData(x)
#define ifDebugGetComponentById(x)
#define ifDebugGetChildPageByPath(x)
#define ifDebugGetRecursiveChildComponents(x)
#define ifDebugGetHome(x)

int ComponentData::count = 0;
QHash<const ComponentDataRoot*, QHash<QString, ComponentData*> > ComponentData::m_idHash;
QHash<const ComponentDataRoot*, QMultiHash<IndexedString, ComponentData*> > ComponentData::m_dbIdHash;
QHash< QPair<const ComponentDataRoot*, ComponentClass>, QList<ComponentData*> > ComponentData::m_componentClassHash;
QSet< QPair<const ComponentDataRoot*, ComponentClass> > ComponentData::m_componentsByClassRequested;
QList<ComponentData*> ComponentData::m_homes;
QMultiHash<QThread*, ComponentData*> ComponentData::m_uncachedDatas;

ComponentData::ComponentData(Generator* generator, ComponentData* parent_, QString componentId_, QString dbId_, ComponentClass componentClass_)
    : m_parent(parent_), m_componentClass(componentClass_), m_generator(generator)
{
    if (m_parent && componentId_.startsWith(m_parent->componentId())) {
        componentId_ = componentId_.mid(m_parent->componentId().length());
        if (componentId_.startsWith('-')) {
            m_idSeparator = Generator::Dash;
        } else if (componentId_.startsWith('_')) {
            m_idSeparator = Generator::Underscore;
        } else {
            Q_ASSERT(0);
        }
        m_childId = componentId_.mid(1);
    } else {
        m_childId = componentId_;
        m_idSeparator = Generator::NoSeparator;
    }

    if (m_parent && dbId_.startsWith(m_parent->dbId())) {
        dbId_ = dbId_.mid(m_parent->dbId().length());
        if (dbId_.startsWith('-')) {
            Q_ASSERT(m_idSeparator == Generator::Dash);
        } else if (dbId_.startsWith('_')) {
            Q_ASSERT(m_idSeparator == Generator::Underscore);
        } else {
            Q_ASSERT(0);
        }
    } else {
        if (dbId_ != componentId()) {
            setDbIdPrefix(dbId_);
        }
    }
    init();
}

ComponentData::ComponentData(Generator* generator, ComponentData* parent_, Generator::IdSeparator separator_, int childId_, ComponentClass componentClass_)
    : m_parent(parent_), m_idSeparator(separator_),
      m_childId(QString::number(childId_)), m_componentClass(componentClass_),
      m_generator(generator)
{
    init();
}

ComponentData::ComponentData(Generator* generator, ComponentData* parent_, Generator::IdSeparator separator_, IndexedString childId_, ComponentClass componentClass_)
    : m_parent(parent_), m_idSeparator(separator_),
      m_childId(childId_.toString()), m_componentClass(componentClass_),
      m_generator(generator)
{
    init();
}

void ComponentData::init()
{
    m_childrenBuilt = false;

    Q_ASSERT(!m_componentClass.isEmpty());

    m_componentClassHash[qMakePair(root(), m_componentClass)] << this;

    ++count;
    if (qobject_cast<ConnectionThread*>(QThread::currentThread())) {
        static_cast<ConnectionThread*>(QThread::currentThread())->componentCreated();
    }

    ifDebugCreateComponentData( qDebug() << count << componentId() << componentClass().toString(); )
    if (m_idSeparator == Generator::NoSeparator) {
        if (m_idHash[root()].contains(componentId())) {
            qWarning() << "componentId exists already" << componentId();
        }
        Q_ASSERT(!m_idHash[root()].contains(componentId()));
        m_idHash[root()][componentId()] = this;
    }

    if (generator()) {
        generator()->builtComponents << this;
    }
    if (generatorFlags() & Generator::DisableCache) {
        m_uncachedDatas.insert(QThread::currentThread(), this);
    }
}

ComponentData::~ComponentData()
{
    count--;
    m_componentClassHash[qMakePair(root(), m_componentClass)].removeAll(this);
    if (m_idSeparator == Generator::NoSeparator) {
        m_idHash[root()].remove(componentId());
    }
    foreach (const IndexedString &key, m_dbIdHash[root()].keys(this)) {
        m_dbIdHash[root()].remove(key, this);
    }
    m_homes.removeAll(this);
    if (m_parent) {
        foreach (int key, m_parent->m_childIdsHash.keys(this)) {
            m_parent->m_childIdsHash.remove(key);
        }
        m_parent->m_children.removeAll(this);
    }
    if (generator()) {
        generator()->builtComponents.removeAll(this);
    }
    foreach (ComponentData *c, m_children) {
        if (c->m_parent == this) {
            delete c;
        } else {
            Q_ASSERT(c->generator()->generatorFlags & Generator::TypeInherit);
        }
    }
}

void ComponentData::addChildren(ComponentData *c)
{
    Q_ASSERT(!(c->generatorFlags() & Generator::DisableCache));
    Q_ASSERT(!m_childrenLock.tryLockForRead());
    m_children << c;
    m_childIdsHash.clear();
}

QList<ComponentData*> ComponentData::getComponentsByClass(const ComponentDataRoot *root, ComponentClass cls)
{
    qDebug() << "getComponentsByClass" << cls << "root:" << root->componentClass();

    //m_componentClassHash wird in ComponentData::init geschrieben
    if (!m_componentsByClassRequested.contains(qMakePair(root, cls))) {

        m_componentsByClassRequested.insert(qMakePair(root, cls));

        if (root->componentClass() == cls) {
            //es darf nur eine root geben
            return m_componentClassHash[qMakePair(root, cls)];
        }

        bool allSupported = true;
        QList<GeneratorTableSqlWithComponent*> generators;
        foreach (Generator *g, Generator::generators(root)) {
            if (g->childComponentClasses().contains(cls)) {
                generators << static_cast<GeneratorTableSqlWithComponent*>(g);
                if (dynamic_cast<GeneratorTableSqlWithComponent*>(g)) {
                    //TODO: support more, by adding an interface or something
                    //(for now we are happy to get paragraphs fast
                    if (g->generatorFlags & Generator::ColumnComponentId) {
                        continue;
                    }
                }
                //qWarning() << "too bad" << g->generatorClass << "is only fallback spported";
                allSupported = false;
            }
        }
        if (allSupported) {
            BuildNoChildrenStrategy s;
            foreach (Generator *g, generators) {
                GeneratorTableSqlWithComponent *gt = static_cast<GeneratorTableSqlWithComponent*>(g);
                foreach (const QString &id, gt->fetchParentDbIds(cls)) {
                    foreach (ComponentData *d, ComponentDataRoot::getComponentsByDbId(root, id)) {
                        Generator::buildWithGenerators(d, &s);
                    }
                }
            }
        } else {
            BuildNoChildrenStrategy s;
            foreach (Generator *g, generators) {
                foreach (ComponentData *d, getComponentsByClass(root, g->componentClass)) {
                    Generator::buildWithGenerators(d, &s);
                }
            }
            /*
            //fallback, nicht so effizient
            BuildOnlyComponentClassStrategy s(cls);
            Generator::buildWithGenerators(ComponentDataRoot::getInstance(), &s);
            */
        }
    }
    return m_componentClassHash[qMakePair(root, cls)];
}


ComponentData* ComponentData::getHome(ComponentData* subRoot)
{
    ifDebugGetHome( qDebug() << "homes" << m_homes; )
    foreach (ComponentData *c, m_homes) {
        ifDebugGetHome( qDebug() << "checking" << c->componentId(); )
        while (!subRoot->hasFlag(IndexedString("subroot"))) {
            if (!subRoot->parent()) break; //use root
            subRoot = subRoot->parent();
        }
        ifDebugGetHome( qDebug() << "subroot" << subRoot->componentId(); )
        ComponentData* i = c;
        do {
            if (i == subRoot) {
                ifDebugGetHome( qDebug() << "MATCH! return" << c->componentId(); )
                return c;
            }
        } while ((i = i->parent()));
        ifDebugGetHome( qDebug() << "doesn't match"; )
    }
    ifDebugGetHome( qDebug() << "no home matched, return 0"; )
    return 0;
}


//TODO: kopie von serialize
QHash< QByteArray, QVariant > ComponentData::dataForWeb() const
{
    QHash<QByteArray, QVariant> ret;
    ret["url"] = url();
    ret["componentId"] = componentId();
    ret["dbId"] = dbId();
    if (parent()) {
        ret["parentId"] = parent()->componentId();
    } else {
        ret["parentId"] = false;
    }
    ret["isPage"] = QVariant(generatorFlags() & Generator::TypePage);
    ret["componentClass"] = componentClass().toString();
    ret["isPseudoPage"] = QVariant(generatorFlags() & Generator::TypePseudoPage);
    ret["priority"] = priority(); 
    ret["box"] = box().toString();
    ret["multiBox"] = multiBox().toString();
    ret["id"] = childId();
    if (generator() && !generator()->model.isEmpty()) {
        ret["model"] = generator()->model.toString();
    } else {
        ret["model"] = false;
    }
    ret["name"] = name();
    ret["tags"] = QVariant(tags());
    ret["inherits"] = QVariant(generatorFlags() & Generator::TypeInherits);
    ret["_filename"] = filename();
    ret["_rel"] = false;
    return ret;

}

//TODO: kopie von dataForWeb
QByteArray serialize(const ComponentData* d)
{
    if (!d) return serialize(NullValue());
    QByteArray ret;
    QByteArray cls("Vps_Component_Data");
    ret += "O:"+QByteArray::number(cls.length())+":\""+cls+"\":17:{";
    ret += serializePrivateObjectProperty("_url", "Vps_Component_Data", d->url());
    ret += serializePrivateObjectProperty("_rel", "Vps_Component_Data", NullValue());
    ret += serializePrivateObjectProperty("_filename", "*", d->filename());
    ret += serializeObjectProperty("componentId", d->componentId());
    ret += serializeObjectProperty("dbId", d->dbId());
    if (d->parent()) {
        ret += serializeObjectProperty("parentId", d->parent()->componentId());
    } else {
        ret += serializeObjectProperty("parentId", NullValue());
    }
    ret += serializeObjectProperty("isPage", d->generatorFlags() & Generator::TypePage);
    ret += serializeObjectProperty("componentClass", d->componentClass().toString());
    ret += serializeObjectProperty("isPseudoPage", d->generatorFlags() & Generator::TypePseudoPage);
    ret += serializeObjectProperty("priority", d->priority());
    ret += serializeObjectProperty("box", d->box());
    ret += serializeObjectProperty("multiBox", d->multiBox());
    ret += serializeObjectProperty("id", d->childId());
    if (d->generator() && !d->generator()->model.isEmpty()) {
        ret += serializeObjectProperty("model", d->generator()->model);
    } else {
        ret += serializeObjectProperty("model", false);
    }
    ret += serializeObjectProperty("name", d->name());
    ret += serializeObjectProperty("tags", d->tags());
    ret += serializeObjectProperty("inherits", d->generatorFlags() & Generator::TypeInherits);
    ret += "}";
    return ret;
}

QList< ComponentData* > ComponentData::recursiveChildComponents(const Select& s, const Select& childSelect)
{
    ifDebugGetRecursiveChildComponents( qDebug() << "====> recursiveChildComponents" << componentId() << s << childSelect; )
    QList<ComponentData*> ret;
    Select s2(s);
    s2.limitCount = 0; //TODO recht ineffizient, aber daf�r simpel
    s2.limitOffset = 0;
    foreach (ComponentData *d, childComponents(s2)) {
        Q_ASSERT(d);
        Q_ASSERT(!d->componentClass().isEmpty());
        ret << d;
        ifDebugGetRecursiveChildComponents( qDebug() << "recursiveChildComponents found" << d->componentId(); )
        if (s.limitCount && ret.count() >= (s.limitCount+s.limitOffset)) {
            break;
        }
    }
    
    ifDebugGetRecursiveChildComponents( qDebug() << "getting childComponents to check recursively" << componentId(); )
    foreach (ComponentData *d, childComponents(childSelect)) {
        if (s2.couldCreateIndirectly(d->root(), d->componentClass())) {
            foreach (ComponentData *c, d->recursiveChildComponents(s2, childSelect)) {
                ret << c;
                if (s.limitCount && ret.count() >= (s.limitCount+s.limitOffset)) {
                    break;
                }
            }
        }
    }
    if (s.limitOffset) {
        ret = ret.mid(s.limitOffset);
    }
    s2.where.clear(); //TODO MEGA HACK ist n�tig damit ned where doppelt gel�scht wird
    return ret;
}

QList< ComponentData* > ComponentData::childComponents(const Select& s)
{
    QList<ComponentData*> ret;
    QList<ComponentData*> chld = children();
    //QTime stopWatch;
    //stopWatch.start();
    foreach (ComponentData *d, chld) {
        if (s.match(d, this)) {
            ret << d;
        }
        if (s.limitCount && ret.count() >= (s.limitCount+s.limitOffset)) {
            qDebug() << "break; limit reached";
            break;
        }
    }
    //qDebug() << "matched children in" << stopWatch.elapsed() << "ms";

    foreach (Generator *g, Generator::generators(root())) {
        if (g->componentClass == componentClass() && g->generatorFlags & Generator::DisableCache) {
            qDebug() << "add for DisabledCache generator" << g->componentClass;
            if (!s.mightMatch(g)) continue;
            qDebug() << "We can't skip it :(" << g->componentClass;
            Select s2(s);
            s2.limitOffset = 0;
            s2.limitCount = 0;
            foreach (ComponentData *d, g->buildDynamic(this, s2)) {
                if (s.match(d, this)) {
                    ret << d;
                }
                if (s.limitCount && ret.count() >= (s.limitCount+s.limitOffset)) {
                    qDebug() << "break; limit reached (2)";
                    break;
                }
            }
            s2.where.clear(); //TODO MEEEEEGA HACK ohne dem w�rden die where objekte doppelt gel�scht werden
                              //Select kopiert die noch nicht korrektr
        }
    }
    if (s.limitOffset) {
        ret = ret.mid(s.limitOffset);
    }
    return ret;
}

ComponentData* ComponentData::childPageByPath(const QString& path)
{
    ifDebugGetChildPageByPath( qDebug() << "childPageByPath" << path << componentId(); )
    Select childSelect;
    childSelect.where << new SelectExprNot(new SelectExprWhereIsPseudoPage());

    ComponentData *page = this;
    if (!path.isEmpty()) {
        foreach (const QString &pathPart, path.split('/')) {
            ifDebugGetChildPageByPath( qDebug() << "pathPart" << pathPart; )
                                                                    //TODO: sch�nere, bessere l�sung n�tig
            if (page==this || page->componentClass().parentClasses().contains(IndexedString("Vpc_Root_DomainRoot_Domain_Component"))) {
                ifDebugGetChildPageByPath( qDebug() << "checking for shortcutUrl" << pathPart; )
                ComponentClass cc = ComponentClass::componentForShortcutUrl(pathPart);
                if (!cc.isEmpty()) {
                    ifDebugGetChildPageByPath( qDebug() << "it is a shortcutUrl" << pathPart; )
                    bool found = false;
                    QList<ComponentData*> components = ComponentData::getComponentsByClass(root(), cc);
                    foreach (ComponentData *c, components) {
                        ComponentData *p = c;
                        do {
                            if (p == page) {
                                page = c;
                                found = true;
                                break;
                            }
                        } while ((p = p->parent()));
                    }
                    if (!found) return 0;
                    continue;
                }
            }
            Select s;
            s.where << new SelectExprWhereFilename(pathPart);
            s.where << new SelectExprWhereIsPseudoPage();
            s.limitCount = 1;
            QList<ComponentData*> pages = page->recursiveChildComponents(s, childSelect);
            if (pages.isEmpty()) {
                ifDebugGetChildPageByPath( qDebug() << "found nothing for " << pathPart; )
                return 0;
            }
            page = pages.first();
        }
    }

    ifDebugGetChildPageByPath( qDebug() << "page" << page << "this" << this; )
                                                                      //TODO: sch�nere, bessere l�sung n�tig
    if (page && (page==this || page->componentClass().parentClasses().contains(IndexedString("Vpc_Root_DomainRoot_Domain_Component")))) {
        ifDebugGetChildPageByPath( qDebug() << "looking for home" << page; )
        ifDebugGetChildPageByPath( if (page) qDebug() << "startAt" << page->componentId(); )
        page = ComponentData::getHome(page);
    }

    return page;
}

int ComponentData::_getNextSeperatorPos(const QString& id) {
    int pos = -1;
    if (id.indexOf('-') != -1) pos = id.indexOf('-');
    if (id.indexOf('_') != -1) {
        if (pos == -1 || pos > id.indexOf('_')) {
            pos = id.indexOf('_');
        }
    }
    return pos;
}
ComponentData* ComponentData::getComponentById(const ComponentDataRoot *root, QString id)
{
    int pos = _getNextSeperatorPos(id);
    QString mainId;
    if (pos != -1) {
        mainId = id.left(pos);
        id = id.mid(pos);
    } else {
        if (!m_idHash[root].contains(id)) {
            ifDebugGetComponentById( qDebug() << "not in m_idHash"; )
            return 0;
        }
        return m_idHash[root][id];
    }
    ifDebugGetComponentById( qDebug() << "mainId" << mainId << "restId" << id; )
    if (!m_idHash[root].contains(mainId)) {
        ifDebugGetComponentById( qDebug() << "not in m_idHash"; )
        return 0;
    }
    ComponentData *data = m_idHash[root][mainId];
    return _getChildComponent(data, id);
}

QHash<int, ComponentData*> ComponentData::childIdsHash()
{
    bool ok;
    if (m_childIdsHash.isEmpty()) {
        foreach (ComponentData *c, children()) {
            int childIdInt = c->m_childId.toInt(&ok);
            if (ok) {
                m_childIdsHash[childIdInt * (c->m_idSeparator == Generator::Underscore ? -1 : 1)] = c;
            }
        }
    }
    return m_childIdsHash;
}

QList< ComponentData* > ComponentData::getComponentsByDbId(const ComponentDataRoot* root, QString id)
{
    QString mainId;

    int pos = _getNextSeperatorPos(id);
    if (pos != -1) {
        mainId = id.left(pos);
        id = id.mid(pos);
    } else {
        mainId = id;
        id.clear();
    }
    ifDebugGetComponentById( qDebug() << "mainId" << mainId << "restId" << id; )
    QList<ComponentData*> ret;

    //zuerst �ber normale id suchen
    if (m_idHash[root].contains(mainId)) {
        if (!id.isEmpty()) {
            ComponentData *d = _getChildComponent(m_idHash[root][mainId], id);
            if (d) ret << d;
        } else {
            ret << m_idHash[root][mainId];
        }
    }
    ifDebugGetComponentById( qDebug() << "count found using m_idHash" << ret.count(); )

    ifDebugGetComponentById( qDebug() << m_dbIdHash[root]; )
    //dann �ber dbId
    foreach (ComponentData *data, m_dbIdHash[root].values(IndexedString(mainId))) {
        ifDebugGetComponentById( qDebug() << "m_dbIdHash entry" << data->componentId(); )
        if (!id.isEmpty()) {
            ComponentData *d = _getChildComponent(data, id);
            if (d) {
                ifDebugGetComponentById( qDebug() << "<<<<<<<<<found>>>>>>" << d->componentId(); )
                ret << d;
            }
        } else {
            ret << data;
        }
    }
    ifDebugGetComponentById( qDebug() << "count found" << ret.count(); )
    return ret;
}

ComponentData* ComponentData::_getChildComponent(ComponentData* data, QString id)
{
    if (id.isEmpty()) return 0;
    while (!id.isEmpty()) {
        Generator::IdSeparator sep;
        if (id.left(1) == "_") {
            sep = Generator::Underscore;
        } else {
            sep = Generator::Dash;
        }
        id = id.mid(1);
        int pos = _getNextSeperatorPos(id);
        QString idPart;
        if (pos != -1) {
            idPart = id.left(pos);
            id = id.mid(pos);
        } else {
            idPart = id;
            id.clear();
        }

        ifDebugGetComponentById( qDebug() << "idPart" << idPart << "restId" << id; )

        bool found = false;

        bool ok;
        int idPartInt = idPart.toInt(&ok);
        if (ok) {
            idPartInt *= (sep == Generator::Underscore ? -1 : 1);
            if (data->childIdsHash().contains(idPartInt)) {
                data = data->childIdsHash()[idPartInt];
                found = true;
            }
        } else {
            foreach (ComponentData *c, data->children()) {
                if (c->m_idSeparator == sep && idPart == c->m_childId) {
                    data = c;
                    found = true;
                    break;
                }
            }
        }
        if (!found) {
            foreach (Generator *g, Generator::generators(data->root())) {
                if (g->componentClass == data->componentClass() && g->generatorFlags & Generator::DisableCache) {
                    Select s;
                    s.where << new SelectExprWhereIdEquals((sep == Generator::Underscore ? '_' : '-')+idPart);
                    s.limitCount = 1;
                    foreach (ComponentData *d, g->buildDynamic(data, s)) {
                        data = d;
                        found = true;
                    }
                }
            }
        }
        if (!found) {
            ifDebugGetComponentById( qDebug() << "nothing found in children"; )
            ifDebugGetComponentById( qDebug() << "looking for" << sep << idPart << "in" << data->componentId(); )
            return 0;
        }
    }
    return data;
}

QString ComponentData::filename() const
{
    if (!generator()) return QString(); //root

    if (generatorFlags() & Generator::UniqueFilename) {
        return m_filename;
    }
    return childId()+"_"+m_filename;
}

void ComponentData::setFilename(const QString& filename)
{
    m_filename = filename;
    m_filename = m_filename.toLower();
    m_filename.replace(QRegExp("[^a-z0-9]+"), "_");
}

void ComponentData::setName(const QString& name)
{
    m_name = name;
}

void ComponentData::setIsHome(bool isHome)
{
    if (isHome) {
        if (!m_homes.contains(this)) m_homes << this;
    } else {
        if (m_homes.contains(this)) m_homes.removeAll(this);
    }
}

QString ComponentData::url() const
{
    if (!(generatorFlags() & Generator::TypePage)) {
        const ComponentData *p = page();
        if (p) return p->url();
        return QString();
    }

    if (isHome()) {
        return "/";
    }
    QStringList filenames;
    const ComponentData *page = this;
    do {
        if (!filenames.isEmpty() && page->hasFlag(IndexedString("shortcutUrl"))) {
            filenames.prepend(page->componentClass().shortcutUrl().toString());
            break;
        } else {

            //TODO: das ist ein schircher hack; das war fr�her im Data von dieser komponente
            if (page->componentClass().parentClasses().contains(IndexedString("Vpc_Root_DomainRoot_Domain_Component"))) continue;

            if (!page->filename().isEmpty()) filenames.prepend(page->filename());
        }
    } while ((page = page->parentPseudoPageOrRoot()));
    return "/" + filenames.join("/");
}

const ComponentData* ComponentData::pseudoPageOrRoot() const
{
    const ComponentData *page = this;
    while (page && !(page->generatorFlags() & Generator::TypePseudoPage)) {
        if (!page->parent()) return page;
        page = page->parent();
    }
    return page;
}

const ComponentDataRoot* ComponentData::root() const
{
    const ComponentData *page = this;
    forever {
        if (!page->parent()) {
            return static_cast<const ComponentDataRoot*>(page);
        }
        page = page->parent();
    }
}

