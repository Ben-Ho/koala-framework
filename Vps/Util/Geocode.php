<?php
class Vps_Util_Geocode
{
    /**
     * Gibt die Geokoordinaten anhand einer Adresse zurück
     *
     * @param string $address Die Adresse die geocodet werden woll
     * @return array|null $geocode Ein Array mit key 'lat' und 'lng'
     */
    public static function getCoordinates($address)
    {
        $apiKey = Vps_Assets_GoogleMapsApiKey::getKey();
        $q = $address;
        $q = str_replace(array('ä','ö','ü','Ä','Ö','Ü','ß'), array('ae','oe','ue','Ae','Oe','Ue','ss'), $q);
        $getParams = array(
            'q' => $q,
            'output' => 'json',
            'key' => $apiKey
        );

        $client = new Zend_Http_Client("http://maps.google.com/maps/geo");
        $client->setMethod(Zend_Http_Client::GET);
        $client->setParameterGet($getParams);
        $body = utf8_encode($client->request()->getBody());
        $result = Zend_Json::decode($body);

        if (isset($result) && isset($result['Placemark']) && isset($result['Placemark'][0])
            && isset($result['Placemark'][0]['Point']) && isset($result['Placemark'][0]['Point']['coordinates'])
            && isset($result['Placemark'][0]['Point']['coordinates'][0])
            && isset($result['Placemark'][0]['Point']['coordinates'][1])
        ) {
            return array(
                'lat' => $result['Placemark'][0]['Point']['coordinates'][1],
                'lng' => $result['Placemark'][0]['Point']['coordinates'][0]
            );
        }
        return null;
    }

}
