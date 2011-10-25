<?php
class Kwf_Controller_Action_Cli_Web_FulltextController extends Kwf_Controller_Action_Cli_Abstract
{
    public static function getHelp()
    {
        return "various fulltext index commands";
    }

    public function termsAction()
    {
        //d(Kwf_Util_Fulltext::getInstance()->terms());
        $i = Kwf_Util_Fulltext::getInstance();
        $i->resetTermsStream();
        $i->skipTo(new Zend_Search_Lucene_Index_Term('w', 'title'));
        while ($i->currentTerm()) {
            p($i->currentTerm());
            $i->nextTerm();
        }
        $i->closeTermsStream();
        exit;
    }

    public function optimizeAction()
    {
        Kwf_Util_Fulltext::getInstance()->optimize();
        exit;
    }

    public function checkForInvalidAction()
    {
        $this->_checkForInvalid();
        echo "\noptimize index...\n";
        Kwf_Util_Fulltext::getInstance()->optimize();
        echo "done.\n";
        exit;
    }

    private function _checkForInvalid()
    {
        $index = Kwf_Util_Fulltext::getInstance();
        echo "numDocs: ".$index->numDocs()."\n";
        $query = Zend_Search_Lucene_Search_QueryParser::parse('dummy:dummy');
        echo "checking: ".count($index->find($query))."\n";
        $c = new Zend_ProgressBar_Adapter_Console();
        $c->setElements(array(Zend_ProgressBar_Adapter_Console::ELEMENT_PERCENT,
                                Zend_ProgressBar_Adapter_Console::ELEMENT_BAR,
                                Zend_ProgressBar_Adapter_Console::ELEMENT_ETA));
        $progress = new Zend_ProgressBar($c, 0, count($index->find($query)));
        foreach ($index->find($query) as $doc) {
            $progress->next();
            if (!Kwf_Component_Data_Root::getInstance()->getComponentById($doc->componentId)) {
                echo "\n$doc->componentId ist im index aber nicht im Seitenbaum, wird gelöscht...\n";
                $index->delete($doc->id);
                $m = Kwf_Model_Abstract::getInstance('Kwc_FulltextSearch_MetaModel');
                $row = $m->getRow($doc->componentId);
                if ($row) {
                    $row->delete();
                }
            }
        }
        $progress->finish();
    }

    public function rebuildAction()
    {
        system("php bootstrap.php fulltext check-for-invalid");

        $queueFile = 'temp/fulltextRebuildQueue';

        $componentId = 'root';
        if ($this->_getParam('componentId')) $componentId = $this->_getParam('componentId');
        file_put_contents($queueFile, $componentId);
        while(true) {
            $pid = pcntl_fork();
            if ($pid == -1) {
                throw new Kwf_Exception("fork failed");
            } else if ($pid) {
                //parent process
                pcntl_wait($status); //Schützt uns vor Zombie Kindern
                if ($status != 0) {
                    throw new Kwf_Exception("child process failed");
                }

                //echo "memory_usage (parent): ".(memory_get_usage()/(1024*1024))."MB\n";
                if (!file_get_contents($queueFile)) {
                    echo "fertig.\n";
                    break;
                }
            } else {

                while (true) {
                    //child process

                    //echo "memory_usage (child): ".(memory_get_usage()/(1024*1024))."MB\n";
                    if (memory_get_usage() > 50*1024*1024) {
                        echo "new process...\n";
                        break;
                    }

                    $queue = file_get_contents($queueFile);
                    if (!$queue) break;

                    $queue = explode("\n", $queue);
                    //echo "queued: ".count($queue)."\n";
                    $componentId = array_shift($queue);
                    file_put_contents($queueFile, implode("\n", $queue));

                    //echo "==> ".$componentId."\n";
                    $page = Kwf_Component_Data_Root::getInstance()->getComponentById($componentId);
                    if (!$page) {
                        echo "$componentId not found!\n";
                        continue;
                    }
                    //echo "$page->url\n";
                    foreach ($page->getChildPseudoPages(array(), array('pseudoPage'=>false)) as $c) {
                        //echo "queued $c->componentId\n";
                        $queue[] = $c->componentId;
                        file_put_contents($queueFile, implode("\n", $queue));
                    }

                    if (!$page->isPage) continue;

                    //echo "checking for childComponents\n";
                    $fulltextComponents = $page->getRecursiveChildComponents(array('flag'=>'hasFulltext'));
                    if (Kwc_Abstract::getFlag($page->componentClass, 'hasFulltext')) {
                        $fulltextComponents[] = $page;
                    }
                    if ($fulltextComponents) {
                        echo " *** indexing $page->componentId $page->url...";
                        $index = Kwf_Util_Fulltext::getInstance();

                        $doc = new Zend_Search_Lucene_Document();

                        //whole content, for preview in search result
                        $doc->addField(Zend_Search_Lucene_Field::UnIndexed('content', '', 'utf-8'));

                        //normal content with boost=1 goes here
                        $doc->addField(Zend_Search_Lucene_Field::UnStored('normalContent', '', 'utf-8'));

                        $t = $page->getTitle();
                        if (substr($t, -3) == ' - ') $t = substr($t, 0, -3);
                        $field = Zend_Search_Lucene_Field::Text('title', $t, 'utf-8');
                        $field->boost = 10;
                        $doc->addField($field);

                        foreach ($fulltextComponents as $c) {
                            if (method_exists($c->getComponent(), 'modifyFulltextDocument')) {
                                $doc = $c->getComponent()->modifyFulltextDocument($doc);
                            }
                            //Komponente kann null zurückgeben um zu sagen dass gar nicht indiziert werden soll
                            if (!$doc) {
                                echo " [no $c->componentId $c->componentClass]";
                                break;
                            }
                        }
                        if (!$doc->getField('content')->value) {
                            echo " [no content]";
                            $doc = null;
                        }
                        echo "\n";

                        if ($doc) {
                            //das wird verwendet um alle dokumente im index zu finden
                            //ned wirklisch a schöne lösung :(
                            $field = Zend_Search_Lucene_Field::UnStored('dummy', 'dummy', 'utf-8');
                            $field->boost = 0.0001;
                            $doc->addField($field);

                            $field = Zend_Search_Lucene_Field::Keyword('componentId', $page->componentId, 'utf-8');
                            $field->boost = 0.0001;
                            $doc->addField($field);

                            $subRoot = $page;
                            while ($subRoot) {
                                if (Kwc_Abstract::getFlag($subRoot->componentClass, 'subroot')) break;
                                $subRoot = $subRoot->parent;
                            }
                            if ($subRoot) {
                                //echo "subroot $subRoot->componentId\n";
                                $field = Zend_Search_Lucene_Field::Keyword('subroot', $subRoot->componentId, 'utf-8');
                                $field->boost = 0.0001;
                                $doc->addField($field);
                            }
                            if ($this->_getParam('debug')) {
                                foreach ($doc->getFieldNames() as $fieldName) {
                                    echo "$fieldName: ".substr($doc->$fieldName, 0, 80)."\n";
                                    //echo "$fieldName: ".$doc->$fieldName."\n";
                                }
                            }

                            $term = new Zend_Search_Lucene_Index_Term($page->componentId, 'componentId');
                            $hits = $index->termDocs($term);
                            foreach ($hits as $id) {
                                //echo "deleting $hit->componentId\n";
                                $index->delete($id);
                            }

                            $index->addDocument($doc);

                            $m = Kwf_Model_Abstract::getInstance('Kwc_FulltextSearch_MetaModel');
                            $row = $m->getRow($page->componentId);
                            if (!$row) {
                                $row = $m->createRow();
                                $row->page_id = $page->componentId;
                            }
                            $row->indexed_date = date('Y-m-d H:i:s');
                            $row->save();
                        }
                    }
                }
                //echo "child finished\n";
                exit(0);
            }
        }
        echo "optimizing...\n";
        Kwf_Util_Fulltext::getInstance()->optimize();
        exit;
    }

    public function searchAction()
    {
        $index = Kwf_Util_Fulltext::getInstance();

        echo "indexSize ".$index->count()."\n";
        echo "numDocs ".$index->numDocs()."\n";

        $start = microtime(true);

        $queryStr = $this->_getParam('query');
        $query = Zend_Search_Lucene_Search_QueryParser::parse($queryStr);

        $userQuery = Zend_Search_Lucene_Search_QueryParser::parse($queryStr);
        $query = new Zend_Search_Lucene_Search_Query_Boolean();
        $query->addSubquery($userQuery, true /* required */);

        if ($this->_getParam('subroot')) {
            $pathTerm  = new Zend_Search_Lucene_Index_Term($this->_getParam('subroot'), 'subroot');
            $pathQuery = new Zend_Search_Lucene_Search_Query_Term($pathTerm);
            $query->addSubquery($pathQuery, true /* required */);
        }
        if ($this->_getParam('news')) {
            $pathTerm  = new Zend_Search_Lucene_Index_Term('kwcNews', 'kwcNews');
            $pathQuery = new Zend_Search_Lucene_Search_Query_Term($pathTerm);
            $query->addSubquery($pathQuery, true /* required */);
        }


        $hits = $index->find($query);
        echo "searched in ".(microtime(true)-$start)."s\n";

        foreach ($hits as $hit) {
            echo "score ".$hit->score."\n";
            echo "  componentId: ".$hit->componentId."\n";
            echo "\n";
        }
        exit;
    }
}
