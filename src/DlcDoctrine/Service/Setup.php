<?php
namespace DlcDoctrine\Service;

use DlcBase\Service\AbstractSetupService;
use Zend\Console\ColorInterface as Color;

class Setup extends AbstractSetupService
{
    /**
     * Returns an array with the database connection parameters
     * 
     * @return array
     */
    public function getDbConnectionParams()
    {
        return $this->getServiceLocator()
                    ->get('doctrine.entitymanager.orm_default')
                    ->getConnection()
                    ->getParams();
    }
    
    /**
     * Returns the mysql connection command
     * 
     * @return string
     */
    public function getDbConnectionCmd()
    {
        $dbParams = $this->getDbConnectionParams();
        
        $dbConnectionCmd = 'mysql -u ' . $dbParams['user']
                         . ' -h ' . $dbParams['host']
                         . ' -P ' . $dbParams['port'];
        
        if (strlen($dbParams['password']) > 0) {
            $dbConnectionCmd .= ' -p"' . $dbParams['password'] . '"';
        }
        
        return $dbConnectionCmd;
    }
    
    /**
     * Run the module setup
     *
     * This method will be called if the ZFTool setup modules command will be executed.
     */
    public function runSetup()
    {
        if ($this->params->forceIsActive) {
            $this->consoleWriteLineWithIndent('Force mode active! Database will be generated', Color::YELLOW);
            
            $dbParams          = $this->getDbConnectionParams();    
            $dbConnectionCmd   = $this->getDbConnectionCmd();
            $dropDatabaseCmd   = $dbConnectionCmd . ' -e"DROP DATABASE ' . $dbParams['dbname'] . ';"';
            $createDatabaseCmd = $dbConnectionCmd . ' -e"CREATE DATABASE ' . $dbParams['dbname'] . ';"';
            $createSchemaCmd   = 'php bin/doctrine.php orm:schema-tool:create';
            
            $this->consoleWriteWithIndent('Dropping database... ');
            $output = shell_exec($dropDatabaseCmd);
            $this->consoleWriteLine('done!', Color::GREEN);
            
            $this->consoleWriteWithIndent('Creating database... ');
            $output = shell_exec($createDatabaseCmd);
            $this->consoleWriteLine('done!', Color::GREEN);
            
            $this->consoleWriteWithIndent('Creating tables... ');
            $output = shell_exec($createSchemaCmd);
            $this->consoleWriteLine('done!', Color::GREEN);
        } else {
            $this->consoleWriteLineWithIndent('Nothing to do. For database generating please use --force|-f option.', Color::YELLOW);
        }
    }
}