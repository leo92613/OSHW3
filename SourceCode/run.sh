clear
# for size in 10 50 100 500 1000 5000 10000 20000
# 	do
# 		#php file_generate.php $size
# 		for F in 5 10 13 15 18 20 23 25 28 30 
# 			do
# 				php PFF.php process.file $size $F
# 			done


# 	done
for size in 10 50 100 500 1000 5000 10000 20000
	do
	for tmp in 3 5 7 9 10 15 20 25 30 35 40 
		do
			php VSWS.php process.file $size $tmp
		done
    done
