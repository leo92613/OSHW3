<?php
/************************VSWS************************************/
	$file = fopen($argv[1],'r');		
	$running = fgets($file);                               // the amount of pages the process requires.
	$usebit = array();
	$stack = array();
	$M = 5;
	$L = 10;
	$Q = 3;
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

 	As the Q value grows , the min and max resident set size will increase, page faults will decrease.
*/
?>

