<?php

namespace Dbronk\Rabbit;
use PDO;
class DbService
{
    private PDO $pdo;
    private static DbService $instance;
    private string $jobitems = "codeanalyzer_job_items";
    private string $jobs = "codeanalyzer_jobs";
    private static string $settingsFile = "settings.ini";
    public function __construct(){
        $settings = parse_ini_file("settings.ini", true)['database'];

        $this->pdo = new PDO("mysql:host=" . $settings['hostname'] . ";dbname=" . $settings['db'] . ";port=" . $settings['port'],
            $settings['username'],
            $settings['password']);
    }

    public static function setSettingsfile(string $settingsfile): void
    {
        $settingsFile = $settingsfile;
    }
    public function setTables(string $jobsTable, string $jobitemsTable): void
    {
        $this->jobs = $jobsTable;
        $this->jobitems = $jobitemsTable;
    }
    /**
     * Return singleton
     * @return DbService
     */
    public static function getInstance(): DbService
    {
        if(!isset(self::$instance)){
            self::$instance = new DbService();
        }

        return self::$instance;
    }
    public function updateItem(int $id, int $status, int $jobid, string $result = ""):void
    {

       $query = $this->pdo->prepare("UPDATE $this->jobitems SET status = :status, results = :result WHERE id = :id");
       $query->execute([':status' => $status, ':result' => $result, ':id' => $id]);
       $this->jobItemsFinished($jobid);
    }

    private function jobItemsFinished(int $jobid):void
    {
        $count = $this->pdo->prepare("SELECT count(*) FROM $this->jobitems WHERE status = 0 AND job_id = $jobid");
        $count->execute();
        if( $count->fetch()[0] == 0) {
            $this->setInactive($jobid);
        }
    }
    private function setInactive(int $id):void
    {
        $query = "UPDATE $this->jobs SET active = 0 WHERE id = '$id'";
        $this->pdo->exec($query);
    }
}