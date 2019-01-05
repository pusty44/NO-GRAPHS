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

    /** ---------------------------------------------- */
/** ---------------------------------------------- */
    /** ---------------------------------------------- */

    /**
     * Object constructor
     */
    public function __construct()
    {
        $this->matrix = [
            [0.00, 0.08, 0.46, 0.20, 0.15, 0.11],
            [0.00, 0.00, 0.29, 0.08, 0.41, 0.22],
            [0.00, 0.00, 0.00, 0.83, 0.08, 0.09],
            [0.00, 0.00, 0.00, 0.00, 0.71, 0.29],
            [0.00, 0.00, 0.00, 0.00, 0.00, 1.00],
            [0.00, 0.00, 0.00, 0.00, 0.00, 0.00]
        ];
        $this->nodesCount = sizeof($this->matrix);
        $this->pathCounter = 0;
        $this->reliability = [0.34,0.87,0.46,0.15,0.64,0.93];
        $this->alfaVector = [0.39,0.57,26,7,0.34,1];
        $this->betaVector = [0.13,15,76,42,1,7];
        $this->sVector = [21,7,13,8,44,7];
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
                } else if($it == sizeof($localPathList)-1) {
                    echo $path.' || <strong>D:</strong> '.round($this->dVector[$this->pathCounter]*100,2).'%';
                    $this->rList[$this->pathCounter] = $this->countR($this->dVector[$this->pathCounter]);
                    echo ' || <strong>R:</strong> '.round($this->rList[$this->pathCounter],2);
                    echo ' || <strong>Cost:</strong> '.$this->pathCost[$this->pathCounter];
                }
                else echo $path.'->';
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

    /** ---------------------------------------------- */
/** ---------------------------------------------- */
    /** ---------------------------------------------- */

    /**
     * Function to find minimum value in vector
     *
     * @param $vector
     *
     * @return mixed
     */
    function findMin($vector){
        $min = $vector[0];
        foreach($vector as $cost){
            if($cost < $min) $min = $cost;
        }
        return $min;
    }

    /**
     * Function to find maximum value in vector
     *
     * @param $vector
     *
     * @return mixed
     */
    function findMax($vector){
        $max = $vector[0];
        foreach($vector as $r){
            if($r > $max) $max = $r;
        }
        return $max;
    }

    /**
     * Function to create two dimensional matrix with path cost in 0 column and path reliability in 1 column
     * @return mixed
     */
    function createTopsisMatrix(){
        for($i=0;$i<$this->pathCounter;$i++){
            $matrix[$i][0] = $this->pathCost[$i];
            $matrix[$i][1] = $this->rList[$i];
        }
        return $matrix;
    }

    /**
     * Function to normalize matrix for topsis
     *
     * Two dimensional matrix
     * @param $matrix
     *
     * @return mixed
     */
    function normalizeTopsisMatrix($matrix){
        $squaredSum = 0.00;
        $squaredSumArray = array();
        for($i=0;$i<sizeof($matrix);$i++){
            for($j=0;$j<sizeof($matrix[$i]);$j++){
                $squaredSum += pow($matrix[$i][$j],2);
            }
            $squaredSumArray[$i] = sqrt($squaredSum);
        }
        for($i=0;$i<sizeof($matrix);$i++){
            for($j=0;$j<sizeof($matrix[$i]);$j++){
                $matrix[$i][$j] = $matrix[$i][$j] / $squaredSumArray[$i];
            }
        }
        return $matrix;
    }

    /**
     * Function to create weighted values in topsis matrix
     *
     * Two dimensional matrix
     * @param $matrix
     *
     * @return mixed
     */
    function weightedTopsisMatrix($matrix){
        $matrix = $this->normalizeTopsisMatrix($matrix);
        $weighted = array();
        for($i=0;$i<sizeof($matrix);$i++) $weighted[$i] = 1/sizeof($matrix);
        for($i=0;$i<sizeof($matrix);$i++){
            for($j=0;$j<sizeof($matrix[$i]);$j++){
                $matrix[$i][$j] *= $weighted[$i];
            }
        }
        return $matrix;
    }

    /**
     * Function to find best and worst path options
     *
     * Two dimensional matrix
     * @param $matrix
     * 1 Best option / 0 Worst option
     * @param $directions
     */
    function idealTopsis($matrix,$directions){
        $best = 0.00;
        $worst = 0.00;
        for($i=0;$i<sizeof($matrix);$i++){
            if($directions){
                $best = $this->findMax($matrix[$i]);
                $worst = $this->findMin($matrix[$i]);
            }
            else {
                $best = $this->findMin($matrix[$i]);
                $worst = $this->findMax($matrix[$i]);
            }
            $this->bestArr[$i] = $best;
            $this->worstArr[$i] = $worst;
        }
    }

    /**
     * Function to calculate distances in topsis matrix to find best path
     *
     * Two dimensional matrix
     * @param $matrix
     * 1 PLUS / 0 MINUS
     * @param $direction
     *
     * @return array
     */
    function calculateDistance($matrix,$direction){
        $sum = 0.00;
        $sums = array();
        for($i=0;$i<sizeof($matrix[0]);$i++){
            for($j=0;$j<sizeof($matrix);$j++){
                if($direction) $sum += pow($matrix[$j][$i]- $this->bestArr[$j],2);
                else $sum += pow($matrix[$j][$i]- $this->worstArr[$j],2);
            }
            array_push($sums,sqrt($sum));
            $sum = 0.00;
        }
        return $sums;
    }

    /**
     * Function to find best path in graph based on distances
     *
     * Best options
     * @param $plus
     * Worst options
     * @param $minus
     *
     * @return array
     */
    function performanceScore($plus,$minus){
        $perf = array();
        for($i=0;$i<sizeof($plus);$i++){
            array_push($perf,$plus[$i]/($plus[$i] + $minus[$i]));
        }
        return $perf;
    }

    /**
     * Function to print best path option
     *
     * array with performance scores
     * @param $perf
     */
    function printResult($perf){
        $choosen = 0;
        for($i=0;$i<sizeof($perf);$i++){
            if($choosen < $perf[$i]) $choosen = $perf[$i];
        }
        echo 'Ścieżka nr: <strong>'.$choosen.'</strong> jest najlepsza.';
    }

    /** ---------------------------------------------- */
/** ---------------------------------------------- */
    /** ---------------------------------------------- */

    /**
     * Function to initialise algorithm
     */
    function init(){
        $this->initAdjList();
        $this->findAllEdges();
        $this->printAllPaths(0,$this->nodesCount-1);
        echo '<strong>LICZBA DRÓG: '.$this->pathCounter.'</strong>';
        echo '<br />';
        $matrix = $this->createTopsisMatrix();
        $matrix = $this->weightedTopsisMatrix($matrix);
        $this->idealTopsis($matrix,true);
        $plus = $this->calculateDistance($matrix,1);
        $minus = $this->calculateDistance($matrix,0);
        $perf = $this->performanceScore($plus,$minus);
        $this->printResult($perf);
    }
}