<?php
/**
 * Created by PhpStorm.
 * User: Serhii
 * Date: 05.12.17
 * Time: 19:05
 */

namespace Controller;


class Task
{
    private $idTask;
    private $idResources;
    private $Task;
    private $LevelTo;
    private $ResourceTo;

    public function __construct($idTask, $idResources, $Task, $LevelTo, $ResourceTo)
    {
        $this->idTask = $idTask;
        $this->idResources = $idResources;
        $this->Task = $Task;
        $this->LevelTo = $LevelTo;
        $this->ResourceTo = $ResourceTo;
    }

    public function getidTask(){
        return $this->idTask;
    }
    public function getidResources(){
        return $this->idResources;
    }
    public function getTask(){
        return $this->Task;
    }
    public function getLevelTo(){
        return $this->LevelTo;
    }
    public function getResourceTo(){
        return $this->ResourceTo;
    }

}