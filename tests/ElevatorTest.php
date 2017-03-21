<?php
use App\Elevator;
use App\Log;
require 'src/Elevator.class.php';
require 'src/Log.class.php';
class ElevatorTest extends PHPUnit_Framework_TestCase{
	protected $elevator;
	protected $current_floor=1;
	protected $nFloors=7;
	//set true if you want to enable verbose
	private $verbose = false;
	/**
	 * Setup the Elevator number of floor and the current floor
	 */
	public function __construct(){
		Log::reset();
		echo 'Elevator __construct'.PHP_EOL;
		$this->nFloors = 7;
		$this->current_floor = 1;
		$this->elevator = new Elevator($this->current_floor,$this->nFloors);
		if($this->verbose===true){
			$this->elevator->enableDebug();
		}
		$this->assertEquals('object', gettype($this->elevator));
		echo 'Number of floors: '.$this->nFloors.PHP_EOL;
		echo 'Current floor:    '.$this->current_floor.PHP_EOL;		
		echo '-----------------------'.PHP_EOL;
	}
	/**
	 * Check the number of floors and the current floor
	 */
    public function testInit(){
    	echo 'Elevator init'.PHP_EOL;
		//Current Floor
		$this->assertEquals($this->current_floor, $this->elevator->getCurrentFloor());
		//Total Floors in the building
		$this->assertEquals($this->nFloors, $this->elevator->getTotalFloors());
		echo '-----------------------'.PHP_EOL;
    }
    /**
     * Verify if the maintenance floors works
     */
    public function testMaintenance(){
    	echo 'Maintenance Floors'.PHP_EOL;
    	$maintenance_floors = array(2,4);    	
    	foreach($maintenance_floors as $floor){
    		echo 'F'.$floor.PHP_EOL;
    		$this->assertTrue($this->elevator->setFloorInMaintenance($floor));
    	}
    	$this->assertEquals($maintenance_floors,$this->elevator->getMaintenanceFloors());
    	echo '-----------------------'.PHP_EOL;
    }
    /**
     * Add requests to the queue and Move;
     */
    public function testQueue(){
    	echo 'Test Queue'.PHP_EOL;
    	$requests = array(
    		array('from'=>1,'to'=>7),
    		array('from'=>5,'to'=>7),
    		array('from'=>3,'to'=>1),
    		array('from'=>6,'to'=>1)  		
    	);
    	foreach($requests as $floor){
    		echo 'From '.$floor['from'].' to '.$floor['to'].PHP_EOL;
    		$this->assertTrue($this->elevator->addQueue($floor['from'],$floor['to']));
    	}
    	$queue = $this->elevator->getQueue();
    	echo var_export($queue,true).PHP_EOL;
    	echo 'UP   : '.var_export($queue['up'],true).PHP_EOL;
    	echo 'DOWN : '.var_export($queue['down'],true).PHP_EOL;
    	$this->assertEquals(array(1,5,7),$queue['up']);
    	$this->assertEquals(array(6,3,1),$queue['down']);
    	$steps = array(1,1,5,7,6,3,1,1);
    	for($i=0;$i<8;$i++){
	    	$before = $this->elevator->getCurrentFloor();
	    	$this->assertEquals($steps[$i], $before);
	    	$this->elevator->nextFloor();
	    	echo $before.' to '.$this->elevator->getCurrentFloor().PHP_EOL;
    	}
    	echo '------------------'.PHP_EOL;
    }
    
}