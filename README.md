# giant_outsource_test

- Before you start
* You will need to install sqlite3 in order to run this code

- To Run the code
* Type in `php myscript.php -file=test.txt -out=out.csv` in the terminal in the main folder of this project
* You can change the file name to the one you want to read from change `-file=test.txt` to `-file=yourfilename.txt`
* Remember to put the file you wish to read from in the root of this folder
* You can change the output file name as well, change `-out=out.csv` to `-out=fileoutname.csv`
* Results written to the CSV can be found in folder results with the corresponding timestamp - this will be echo'd out in the terminal

- Troubleshoot
* If you have error `PDOException “could not find driver”` use command `sudo apt-get install sqlite php-sqlite3`