PHP CUSTOM ERROR/EXCEPTION HANDLER CLASS

PHP 5
 
@package     Custom Error Class
@category    Error Handling
@author      Liam Best <liam@liambest.com>
@author      David Miles <david@amereservant.com>
@copyright   2010 Liam Best / David Miles
@license     http://creativecommons.org/licenses/MIT/ MIT License
@version     0.1 alpha
@since       April 6, 2010

NOTES:
This project is still being developed.  
The class is not fully mature yet, so it shouldn't be used at this point unless
you are forking or can fill in the blanks.
UPDATES:
Apr. 08, 2010 -
    * Major modifications made to errorhandler.class.php
    * Project config.class.php added and used by the errorhandler.class.php
    * config.ini required for settings for the errorhandler.class.php which is
      processed by and part of config.class.php
    * Added errorhandler.simple.class.php, which is Liam's version and where a large
      part of errorhandler.class.php's code came from.  
      Liam's version works as a stand-alone and doesn't use any of the other files
      in this project.
    * testdoc.php was added which is used to test the errorhandler.class.php and will
      be removed accordingly after development is finished.
