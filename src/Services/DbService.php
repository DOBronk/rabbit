<?php

namespace Dbronk\Rabbit\Services;

use Dbronk\Rabbit\Utilities\DatabaseSettings;
use PDO;

class DbService
{
    private PDO $pdo;
    private static DbService $instance;
    private string $jobitems = "codeanalyzer_job_items";
    private string $jobs = "codeanalyzer_jobs";
    private static DatabaseSettings $settings;

    public function __construct()
    {
        $this->pdo = new PDO("mysql:host=" . self::$settings->host . ";dbname=" . self::$settings->database . ";port=" . self::$settings->port,
            self::$settings->user,
            self::$settings->password);
    }

    public static function setSettings(DatabaseSettings $settings): void
    {
        self::$settings = $settings;
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
        if (!isset(self::$instance)) {
            self::$instance = new DbService();
        }

        return self::$instance;
    }

    private function compareItem(int $id, int $status, string $result = ""): bool
    {
        $query = $this->pdo->prepare("SELECT * FROM $this->jobitems WHERE id = :id");
        $query->execute([':id' => $id]);
        if ($query->rowCount() > 0) {
            $row = $query->fetch();
            if ($row['status_id'] === $status && $row['results'] === $result) {
                return true;
            }
        }
        return false;
    }

    public function updateItem(int $id, int $status, int $jobid, string $result = ""): bool
    {
        if ($this->compareItem($id, $status, $result)) {
            // Same data already exists in database
            return true;
        }
        $query = $this->pdo->prepare("UPDATE $this->jobitems SET status_id = :status_id, results = :result WHERE id = :id");
        $query->execute([':status_id' => $status, ':result' => $result, ':id' => $id]);
        $this->jobItemsFinished($jobid);
        // Return false when no row affected
        return $query->rowCount() > 0;
    }

    public function jobStatus(int $jobid): int
    {
        $status = $this->pdo->prepare("SELECT active FROM $this->jobs WHERE id = $jobid");
        $status->execute();
        if($status->rowCount() > 0){
           return $status->fetch()[0];
        }

        return 0;
    }

    private function jobItemsFinished(int $jobid): void
    {
        $count = $this->pdo->prepare("SELECT count(*) FROM $this->jobitems WHERE status_id = 0 AND job_id = $jobid");
        $count->execute();
        if ($count->fetch()[0] == 0) {
            $this->setInactive($jobid);
        }
    }

    private function setInactive(int $id): void
    {
        $query = "UPDATE $this->jobs SET active = 0 WHERE id = '$id'";
        $this->pdo->exec($query);
    }
}