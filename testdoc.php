<?php
require 'config.class.php';
require 'errorhandler.class.php';
ErrorHandler::test();

/** Trigger Output **/
ErrorHandler::$display = true;
ErrorHandler::getErrors('both');

//Config::devDump();
