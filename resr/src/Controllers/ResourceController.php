<?php
/**
 * Created by PhpStorm.
 * User: Serhii
 * Date: 04.12.17
 * Time: 23:11
 */
namespace Controller;
/**
 * MySQLController - importowanie clasy controli BD
 * Resource - importowanie klasy pojedynćzego objektu typu Resource
 */
use function PHPSTORM_META\type;

include_once __DIR__."/MySQLController.php";
include_once __DIR__."/../Class/Resource.php";
include_once __DIR__."/FactoryInstanceController.php";
include_once __DIR__."/TaskController.php";
include_once __DIR__."/MapController.php";


/**
 * Class ResourceController
 * @package Controller
 * this class contain method to
 * controll "resource" value in
 * Data Base:
 * |---Konstuktor pobiera dane z MySQLController-a
 * |   oraz twoży na tej podstawie tablice objektów
 * |   typu Resource;
 */
class ResourceController
{

    private $ResourceList = array();
    static private $instance = null;
    private $__dataBase__controller;
    private $ResourceListForCurrentUser = array();
    public $SESSION_RESOURCES_LIST = array();


    //funkcja tworzy konstruktora, gdy go nie ma
    public static function getInstance(){
        if(empty(self::$instance))
            self::$instance = new self();
        return self::$instance;
    }

    public function updateProductUnit(string $nameOfResource, int $newProductiveUnit, string $IMG){
        $this->__dataBase__controller->__Admin__ResourcesUpdate($nameOfResource, $newProductiveUnit, $IMG);
    }

    public function deleteResource($name){
        $id = $this->searchID($name);
        if($id != null)
            $this->__dataBase__controller->__Admin__ResourcesRemove($id);
    }



    private function __construct()
    {
        $this->__dataBase__controller = MySQLController::getInstance();
        $this->set($this->__dataBase__controller->__Admin__ResourcesQuery());
    }
    private function set($sql_resources){
        unset($this->ResourceList);
        if (!is_null($sql_resources)) {
            foreach ($sql_resources as $rsr) {
                $this->ResourceList[] = new Resource(
                    $rsr["idResources"],
                    $rsr["Resource"],
                    $rsr["ProductionUnit"],
                    $rsr["FactoryName"],
                    $rsr["IMG"],
                    $rsr["IMGFac"]
                );
            }
        }else{
            //javamessage("W domu dzialalo!");
        }
        return null;
    }
    public function add($Resource, $ProductionUnit, $FactoryName, $IMG, $IMGFac){
        $this->__dataBase__controller->__Admin__ResourcesAdd($Resource, $ProductionUnit, $FactoryName, $IMG, $IMGFac);
        $this->set($this->__dataBase__controller->__Admin__ResourcesQuery());
    }
    public function removeByID($idResource){
        $this->__dataBase__controller->__Admin__ResourcesRemoveByID($idResource);
        $this->set($this->__dataBase__controller->__Admin__ResourcesQuery());
    }
    public function removeByResourceName($Resource){
        $this->__dataBase__controller->__Admin__ResourcesRemoveByName($Resource);
        $this->set($this->__dataBase__controller->__Admin__ResourcesQuery());
    }
    public function update($Resource, $ProductionUnit, $FactoryName, $IMG, $IMGFac){
        $this->__dataBase__controller->__Admin__ResourcesUpdate($Resource, $ProductionUnit, $FactoryName, $IMG, $IMGFac);
        $this->set($this->__dataBase__controller->__Admin__ResourcesQuery());
    }
    public function remove_ALL(){
        $this->set($this->__dataBase__controller->__Admin__ResourcesQuery());
    }
    public function returnArray(){
        return $this->ResourceList;
    }
    private function searchID($name){
        foreach ($this->ResourceList as &$item){
            if ($item->getResourceName() == $name) return $item->getId();
        }
        return null;
    }

    private function setUserResourceArray($idUser=-1){
        //unset($this->ResourceListForCurrentUser);
        $list = $this->__dataBase__controller->__User__UserResource($idUser);
        $this->ResourceListForCurrentUser = array();
        foreach ($list as $value){
            $temp = $this->searchByIDAndReturnObject($value["idResources"]);
//            echo "<pre>";print_r($temp);echo"</pre>";
            if(!is_null($temp)) {
                $this->ResourceListForCurrentUser[] = $temp;
            }
        }
    }

    public function returnArrayForCurrentUserResource($idUser){
        $this->setUserResourceArray($idUser);
        if(empty($this->ResourceListForCurrentUser)){
            return null;
            //javamessage("W domu działało ; - ;");
             } else{
        return $this->ResourceListForCurrentUser;}
    }

    public function searchByID($idres){
        foreach ($this->ResourceList as &$item){
            if ($item->getIdResources() == $idres) return $item->getResourceName();
        }
        return null;
    }
    public function returnArrayByID($idres){
        foreach ($this->ResourceList as &$item){
            if ($item->getIdResources() == $idres) return $item;
        }
        return null;
    }

    public function searchByIDAndReturnObject($idres){
        foreach ($this->ResourceList as $item){
            if ($item->getIdResources() == $idres) return $item;
        }
        return null;
    }

    public $MAP_RESOURCE_COUNT = array();

    public function initializeResourceScoreForFrontEnd(){
        $__Map__ = MapController::getInstance();
        $__factory__ = FactoryInstanceController::getInstance();
        $this->setUserResourceArray($_SESSION["idUser"]);
        $MapList = $__Map__->returnArrayByID($_SESSION["idUser"]);
        if(!empty($MapList)) {
            foreach ($MapList as $value) {
                // Array idResource (ResourceName =>  CountFactory);
                $this->MAP_RESOURCE_COUNT[($__factory__->returnFactoryByID($value->getidFactory()))->getidResource()] = $value->getCountFactory();
            }
        }
        foreach ($this->ResourceListForCurrentUser as $value){
            $this->SESSION_RESOURCES_LIST[] = $value->getResourceName();
            //$_SESSION[$value->getResourceName()] = 0;
        }
    }

    /**
     * create SESSION scope variable by ResourceName param, with farmed resource score.
     * SESSION_RESOURCES_LIST - array contain key to
     */
    public function updateResourceScoreForFrontEnd(){
        $__task__ = TaskController::getInstance();
        $this->SESSION_RESOURCES_LIST = array();
        foreach ($this->ResourceListForCurrentUser as $value){
            if(isset($_SESSION[$value->getResourceName()])) {
                if($_SESSION[$value->getResourceName()] < $__task__->searchLevelByIdResorce($value->getIdResources())) {
                    $_SESSION[$value->getResourceName()] = $_SESSION[$value->getResourceName()] + $value->getProductiveUnit() *
                        ((isset($this->MAP_RESOURCE_COUNT[$value->getIdResources()])? $this->MAP_RESOURCE_COUNT[$value->getIdResources()] : 0));
                }else $_SESSION[$value->getResourceName()] = $__task__->searchLevelByIdResorce($value->getIdResources());
            }else $_SESSION[$value->getResourceName()] = 0;
        }
    }
    public function clearFrontEndResourcesCount(){
        foreach ($this->returnArrayForCurrentUserResource($_SESSION["idUser"]) as $value) {
            if (isset($_SESSION[$value->getResourceName()])) {
                unset($_SESSION[$value->getResourceName()]);
            }
        }
        return true;
    }
}