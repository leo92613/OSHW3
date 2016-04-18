<?php
	$file = fopen($argv[1].$argv[2],'r');	
	$running = fgets($file);                                // the amount of pages the process requires.
	
	echo "The process requires pages: ".$running;
/************************PFF*************************************/
	$usebit = array();
	$stack = array();
	$vtime = 0;
	$F = $argv[3];                                          // set the F value.
	$Max = 0;												// record the max size of resident set.
	$PFF = 0;												// record the times that PFF run.
	$PageFault = 0; 										// record the Page Fault times
	$Min = 0;												// record the min size of resident set.
	echo "F Value: ".$F."\n";
	for($i=0;$i<$running;$i++){	
		$page = fgets($file);
		
		if (in_array($page,$stack)==false){                 // page fault happens.
			$PageFault++;
			if ($vtime>=$F) {                               // virtual time >= F, reset the resident set and the usebit.
				$PFF++;
				foreach ($stack as $key => $value) {
					if ($usebit[$stack[$key]]==0) {
						unset($stack[$key]);
					}
					else if ($usebit[$stack[$key]]==1) {    // virtual time < F, go on the process.
						$usebit[$stack[$key]] = 0;
					}
				}
				if ($PFF==1)
					$Min = sizeof($stack);
				else if (sizeof($stack)<$Min)
					$Min = sizeof($stack);

				$stack[] = $page;
				$usebit[$page] = 1;	
			}
			else if ($vtime<$F) {
				$stack[] = $page;
				$usebit[$page] = 1;	
			}		
			$vtime = 0;
		}
		
		if (in_array($page,$stack)==true) {                // no page fault happens
			$usebit[$page] = 1;                            // set the usebit
			$vtime++;                                      // set the virtual time
		}
		if (sizeof($stack)>$Max)
			$Max = sizeof($stack);
		// echo "resident set size: ".sizeof($stack)."\n";
		//print_r($stack);
		//print_r($usebit);
		//if ($vtime>0)
        //echo "Vtime: ".$vtime."\n";		
	}
	echo "PFF happens: ".$PFF."\n";
	echo "The Max Resident size: ".$Max."\n";
	echo "The Min Resident size: ".$Min."\n";
	echo "The Page fault happens: ".$PageFault."\n"."\n";
	fclose($file);
	

/* In PFF implementation, I thought the F Value should be related to the size of the pages the process occupies.

But the result of the first group experients (Result_1_PFF.txt) disapproves this assumption.


The process requires pages:  the first line of the process file
F Value: the F Value for this experient
PFF happens: the number of times the PFF runs to clean and swap the resident set
The Max Resident size : the maximum size of the resident set
The Min Resident size : the minimum size of the resident set(after the First swap)
The Page fault happens: The number of times page fault happens

When the size is relatively small, PFF happens fewer as the F value grows. However, when the size is relatively large, the times PFF happens is not related to the size. In my case, the PFF doesn't happen at all and the resident set size is extremely large.

Then the second assumption is that F value should be a fixed number.

As is shown in the Result_2_PFF.txt, as F value grows , the min and max resident set size will increase, which burdens the memmory and the times the PFF runs and page faults will decrease, which benefits the CPU.

However, I think in reality, the process.file is way different than randomized array of number. Thus I think the best solution is to set F as dynamic changing with the current state of memory and CPU and the context of process.*/
	

/************************VSWS************************************/
	$file = fopen($argv[1].$argv[2],'r');		
	$running = fgets($file);                               // the amount of pages the process requires.
	$usebit = array();
	$stack = array();
	$M = 10;
	$L = 50;
	$Q = $argv[3];
	$vtime = 0;
	$interval = 0;
	$Max = 0;												// record the max resident set size.
	$VSWS = 0;												// record the times that the VSWS occurs.
	$PageFault = 0; 										// record the Page Fault times
	$Min = 0;												// record the min size of resident set.
	echo "The process requires pages: ".$running;
	echo "M value: ".$M."\n";
	echo "L value: ".$L."\n";
	echo "Q value: ".$Q."\n";
	for ($i=0;$i<$running;$i++) {
		$page = fgets($file);
		$interval++;                                       // increse the interval
		if ($interval==$L) {
			$VSWS++;
			foreach ($stack as $key => $value) {
				if ($usebit[$stack[$key]]==0) {
					unset($stack[$key]);
				}
				else if ($usebit[$stack[$key]]==1) {       //sampling instance reaches L, suspend and scan use bits.
					$usebit[$stack[$key]] = 0;
				}
			}
			if ($VSWS==1)
				$Min = sizeof($stack);
			else if (sizeof($stack)<$Min)
				$Min = sizeof($stack);
			$interval = 0;
			$vtime = 0;						                // reset the virtual time and interval
		}
		if (in_array($page,$stack)==true) {                 // no page fault happens
			$usebit[$page] = 1;                             // set the usebit
		} 
		else if (in_array($page,$stack)==false){
			$PageFault++;
			$vtime++;                                       // set the virtual time
			if ($interval >= $M and $vtime>=$Q) {
				$VSWS++;
				foreach ($stack as $key => $value) {
					if ($usebit[$stack[$key]]==0) {
						unset($stack[$key]);
					}
					else if ($usebit[$stack[$key]]==1) {    // Q page faults occer and M has elapsed, suspend and scan
						$usebit[$stack[$key]] = 0;            
					}
				}
			$interval = 0;
			$vtime = 0;	
			}
			if ($VSWS==1)
				$Min = sizeof($stack);
			else if (sizeof($stack)<$Min)
				$Min = sizeof($stack);
			$stack[] = $page;                               // page fault happens
			$usebit[$page] = 1;	
		}
		if (sizeof($stack)>$Max)
			$Max = sizeof($stack);   
		//echo sizeof($stack)."\n";//." ".$interval." ".$vtime."\n"; 
	}
	echo "VSWS happens: ".$VSWS."\n";
	echo "The Max Resident size: ".$Max."\n";
	echo "The Min Resident size: ".$Min."\n";
	echo "The Page fault happens: ".$PageFault."\n"."\n";
	fclose($file);

	/*
	The result of the experients is saved in (Result_VSWS.txt) .


	The process requires pages:  the first line of the process file
	M value: The M value for this experient
	L value: The L value for this experient
	Q value: The Q value for this experient
	VSWS happens: the number of times the VSWS runs to clean and swap the resident set
	The Max Resident size : the maximum size of the resident set
	The Min Resident size : the minimum size of the resident set(after the First swap)
	The Page fault happens: The number of times page fault happens



	In VSWS, the L value decides the minimum times the VSWS happens, which is (page size)/(L). As L value grows , the min and max resident set size will increase, which burdens the memmory and the times the VSWS runs and page faults will decrease, which benefits the CPU.
 	
 	The M value decides the maximum times the VSWS happens. As M value grows , the min and max resident set size will increase, page faults will decrease.

 	Q value must be set in a scale, if Q is greater than a number, for example L, then Q is barely useless. As the Q value grows , the min and max resident set size will increase, page faults will decrease.
*/

/*

	Compare these two algorithms, PFF generally takes more resident set than the VSWS but the page fault happens less frequently.
	If a PC is short of available memory, VSWS is probably a better choice. However, if the goal is to minimize the page fault, then PFF is better.
	In fact, the process.file I generate to simulate the process is flawed. I believe that the actual case is definately not a array of random number. If we want to learn more about the pros and cons of two algorithms, more experiements is needed.
	
*/

?>


