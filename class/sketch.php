<?php
/**
 * Created by PhpStorm.
 * User: Dawid Pierzak
 * Date: 04.01.2019
 * Time: 16:01
 */
class sketch
{
    /**
     * Initial martix with probability
     * @var array $martix
     */
    public $matrix;
    /**
     * Counter of vertexes
     * @var int
     */
    public $nodesCount;
    /**
     * Martix with vertex neighbours
     * @var array $adjList
     */
    public $adjList = array();
    /**
     * Counts how many paths are found
     * @var int $pathCounter
     */
    public $pathCounter;
    /**
     * Array with D count for every path
     * @var array $dVector
     */
    public $dVector = array();
    /**
     * Vector with reliability for every vertex
     * @var array $reliability
     */
    public $reliability;
    /**
     * Array with R count for every path
     * @var array $rList
     */
    public $rList = array();
    /**
     * Vector with alpha parameter for every vertex
     * @var array $alfaVector
     */
    public $alfaVector;
    /**
     * Vector with beta parameter for every vertex
     * @var array $betaVector
     */
    public $betaVector;
    /**
     * Vector with S parameter for every vertex
     * @var array $sVector
     */
    public $sVector;
    /**
     * Array with cost for every vertex
     * @var array $vertxCost
     */
    public $vertxCost = array();
    /**
     * Array with costs for every found path
     * @var array $pathCost
     */
    public $pathCost = array();
    /**
     * Topsis best values found
     * @var array $bestArr
     */
    public $bestArr = array();
    /**
     * Topsis worst values found
     * @var array $worstArr
     */
    public $worstArr = array();

    public $cVector = array();
    public $kValue = 0;
    public $pathListAdv = array();
    public $rValue = 0;
    public $processed = 0;
    /** ---------------------------------------------- */
/** ---------------------------------------------- */
    /** ---------------------------------------------- */

    /**
     * Object constructor
     */
    public function __construct()
    {
        $this->matrix = [
            [0.00, 0.50, 0.50, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00],
            [0.00, 0.00, 0.00, 0.70, 0.00, 0.00, 0.20, 0.10, 0.00],
            [0.00, 0.00, 0.00, 0.30, 0.70, 0.00, 0.00, 0.00, 0.00],
            [0.00, 0.00, 0.00, 0.00, 0.20, 0.20, 0.50, 0.10, 0.00],
            [0.00, 0.00, 0.00, 0.00, 0.00, 0.50, 0.00, 0.50, 0.00],
            [0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.40, 0.60, 0.00],
            [0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.50, 0.50],
            [0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.20, 0.00, 0.80],
            [0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00]
        ];
        $this->nodesCount = sizeof($this->matrix);
        $this->pathCounter = 0;
        $this->reliability = [1,1,1,1,1,1,1,1,1];
        $this->alfaVector = [50,35,25,30,40,35,20,30,60];
        $this->betaVector = [5,2,8,10,4,3,9,12,5];
        $this->sVector = [1000,400,1500,2500,900,300,1300,2400,800];
        $this->countVertexCost();
    }

    /**
     * Function to initialise matrix with neighbourhood
     */
    function initAdjList(){
        for($i=0;$i<$this->nodesCount;$i++){
            for($j=0;$j<$this->nodesCount;$j++){
                $this->adjList[$i] = array();
            }
        }
    }

    /**
     * Function co count cost of every vertex
     */
    function countVertexCost(){
        for($i=0;$i<$this->nodesCount;$i++){
            $this->vertxCost[$i] = $this->sVector[$i]+($this->alfaVector[$i]*(M_E^($this->betaVector[$i]*$this->reliability[$i])));
        }
    }

    /**
     * Function to print matrix on screen
     *
     * two dimensional matrix must be passed
     * @param $matrix
     */
    function printMatrix($matrix){
        for($i=0;$i<sizeof($matrix);$i++){
            echo '|'.$i.'|';
            for($j=0;$j<sizeof($matrix[$i]);$j++){
                echo $matrix[$i][$j].' ';
            }
            echo '<br />';
        }
    }

    /**
     * Function to add neighbour to vertex
     *
     * Start vertex
     * @param int $u
     * Vertex which is neighbour
     * @param int $v
     */
    function addEdge(int $u, int $v){
        array_push($this->adjList[$u],$v);
    }

    /**
     * Function to find all paths in graph
     *
     * Start vertex
     * @param int $s
     * End vertex
     * @param int $d
     */
    function printAllPaths(int $s, int $d){
        $isVisited = array();
        for($i=0;$i<$this->nodesCount;$i++){
            $isVisited[$i] = false;
        }
        $pathList = array();
        array_push($pathList,$s);
        $this->printAllPathsUtility($s, $d, $isVisited, $pathList,0);
    }

    /**
     * Function to count R for specified vertex in path
     *
     * Value of D parameter in path
     * @param $d
     *
     * @return float|int
     */
    function countR($d){
        $sum = 0;
        foreach($this->reliability as $r){
            $sum = $sum + ($d*$r);
        }
        return $sum;
    }

    /**
     * Function to print all possible paths in graph
     *
     * Start vertex
     * @param int $u
     * End vertex
     * @param int $d
     * Array with visited vertexes
     * @param array $isVisited
     * Array with local possible paths
     * @param array $localPathList
     * Memory of last visited vertex
     * @param $last
     */
    function printAllPathsUtility(int $u, int $d, array $isVisited, array $localPathList, $last){
        $isVisited[$u] = true;
        $this->dVector[$this->pathCounter] = 1;
        $this->pathCost[$this->pathCounter] = 1;
        if($u == $d){
            $it = 0;
            foreach($localPathList as $path){
                $this->dVector[$this->pathCounter] *= $this->matrix[$last][$path];
                $this->pathCost[$this->pathCounter] += $this->vertxCost[$path];
                if($it==0) {
                    $this->dVector[$this->pathCounter] = 1;
                    if(sizeof($localPathList) > 1) echo $path.'->';
                    else echo $path;
                    $this->pathListAdv[$this->pathCounter][] = $path;
                } else if($it == sizeof($localPathList)-1) {
                    echo $path.' || <strong>D:</strong> '.round($this->dVector[$this->pathCounter]*100,2).'%';
                    $this->rList[$this->pathCounter] = $this->countR($this->dVector[$this->pathCounter]);
//                    echo ' || <strong>R:</strong> '.round($this->rList[$this->pathCounter],2);
//                    echo ' || <strong>Cost:</strong> '.$this->pathCost[$this->pathCounter];
                    $this->pathListAdv[$this->pathCounter][] = $path;
                }
                else {
                    echo $path.'->';
                    $this->pathListAdv[$this->pathCounter][] = $path;
                }
                $last = $path;
                $it++;
            }
            $this->pathCounter++;
            echo '<br />';
            $isVisited[$u] = false;
            return;
        }
        foreach($this->adjList[$u] as $i){
            if(!$isVisited[$i]){
                array_push($localPathList, $i);
                $this->printAllPathsUtility($i,$d,$isVisited,$localPathList, $last);
                if (($key = array_search($i, $localPathList)) !== false) {
                    unset($localPathList[$key]);
                }
            }
        }
        $isVisited[$u] = false;
    }

    /**
     * Function to find all edges in grap from probability matrix
     */
    function findAllEdges(){
        for($i=0;$i<$this->nodesCount;$i++){
            for($j=0;$j<$this->nodesCount;$j++){
                if($this->matrix[$i][$j] > 0){
                    $this->addEdge($i,$j);
                }
            }
        }
    }

    /**
     * Function to calculate costs and reliability for path
     */

    function calculateCost(){
        $cost = 0;
        $this->cVector = array();
        $this->kValue = 5000001;
        $this->rValue = 1;
        for($i=0;$i<$this->nodesCount;$i++){
            $cost = $this->sVector[$i] + $this->alfaVector[$i] + pow(M_EULER,$this->betaVector[$i] * $this->reliability[$i]);
            $this->cVector[] = $cost;
            $this->kValue += $cost;
        }
        $tempR = 0;
        for($i=0; $i<sizeof($this->pathListAdv);$i++){
            $tempR = 1;
            for($j=0;$j<sizeof($this->pathListAdv[$i]);$j++){
                $tempR *= $this->reliability[$this->pathListAdv[$i][$j]];
            }
            $tempR *= $this->dVector[$i];
            $this->rValue += $tempR;
        }
        for($i=0;$i<sizeof($this->reliability);$i++){
        }
    }

    /**
     * Recursive function to find best solution
     * @param $cursor
     * @param $iterator
     */
    function checkVariables($cursor,$iterator){
        $this->processed++;
            if($this->kValue <= 5000000 && $this->rValue >= 0.99){
                return ;
            } else {
                if($cursor<$this->nodesCount){
                        if($this->reliability[$cursor]>=0){
                            if($iterator==0) $this->reliability[$cursor] -= 0.001;
                            elseif($iterator==1) $this->reliability[$cursor] -=0.01;
                            elseif($iterator==2) $this->reliability[$cursor] -=0.1;
                        }
                        else {
                            $cursor++;
                            $this->reliability = [1,1,1,1,1,1,1,1,1];
                        }
                        $this->calculateCost();
                        $this->checkVariables($cursor,$iterator);

                } else {
                    if($cursor == $this->nodesCount && $iterator==3) {
                        return;
                    }
                    $cursor = 0;
                    $iterator++;
                }

            }
    }

    /** ---------------------------------------------- */
/** ---------------------------------------------- */
    /** ---------------------------------------------- */

    /**
     * Function to initialise algorithm
     */
    function init(){
        $this->printMatrix($this->matrix);
        echo '<br /><br />';
        $this->initAdjList();
        $this->findAllEdges();
        $this->printAllPaths(0,$this->nodesCount-1);
        echo '<br /><br />';
        echo '<strong>LICZBA DRÓG: '.$this->pathCounter.'</strong>';
        echo '<br /><br />';
        $this->checkVariables(0,0);
        echo 'Koszt całkowity: '.$this->kValue.'<br />';
        echo 'Niez. całkowita: '.$this->rValue.'<br />';
        echo 'Sprawdzonych wariantów: '.$this->processed.'<br />';
    }
}