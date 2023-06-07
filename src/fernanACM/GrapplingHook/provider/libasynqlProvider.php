<?php
    
#      _       ____   __  __ 
#     / \     / ___| |  \/  |
#    / _ \   | |     | |\/| |
#   / ___ \  | |___  | |  | |
#  /_/   \_\  \____| |_|  |_|
# The creator of this plugin was fernanACM.
# https://github.com/fernanACM
    
namespace fernanACM\GrapplingHook\provider;

use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql as libasynqlDB;

use fernanACM\GrapplingHook\GrapplingHook;

class libasynqlProvider{

    /** @var DataConnector $database */
    private DataConnector $database;

    private array $queries = [];
    /**
     * @return void
     */
    public function loadDatabase(): void{
        $plugin = GrapplingHook::getInstance();
        $config = $plugin->config->get("database");
        $this->database = libasynqlDB::create($plugin, $config, [
            "sqlite" => "database/sqlite.sql",
			"mysql"  => "database/mysql.sql"
        ]);
    }

    /**
     * @return void
     */
    public function unloadDatabase(): void{
        if(isset($this->database))
        $this->database->close();
    }

    /**
     * @return DataConnector
     */
    public function getDatabase(): DataConnector{
        return $this->database;
    }
}