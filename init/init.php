<?php
define('ROOT_DIR', dirname(__FILE__) . '/../');

require_once(ROOT_DIR . 'lib/Database/namespace.php');
require_once(ROOT_DIR . 'lib/Common/Logging/Log.php');
require_once(ROOT_DIR . 'lib/Common/ServiceLocator.php');
require_once(ROOT_DIR . 'lib/Config/namespace.php');

// import other init code if needed
// require_once(dirname(__FILE__) . '/add_attributes.php');
// require_once(dirname(__FILE__) . '/create_admin_group.php');
// require_once(dirname(__FILE__) . '/create_schedule.php');
// require_once(dirname(__FILE__) . '/create_resources.php');

// test database connection
function testdb()
{
  try
  {
    Log::Debug("init.php:testdb: test that db connection works.");
    $dbf = new DatabaseFactory();
    $db = $dbf->GetDatabase();
    $dbName = Configuration::Instance()->GetSectionKey(ConfigSection::DATABASE, ConfigKeys::DATABASE_NAME);
    $sql = "SHOW TABLES FROM " . $dbName . ";";
    Log::Debug("init.php:testdb:sql: " . $sql);
    $command = new AdHocCommand($sql);
    $db->Execute($command);
    Log::Debug("init.php:testdb: db connection is ok.");
    return true;
  }
  catch(Exception $e)
  {
    Log::Debug("init.php:testdb: db connection is not ok.");
    Log::Error($e->getMessage());
    return false;
  }
}

function initdb()
{
  if (testdb())
  {
    try
    {
      // this will fail if run twice
      Log::Debug("init.php:initdb try to create table to flag that init has been done.");
      $dbf = new DatabaseFactory();
      $db = $dbf->GetDatabase();
      $command = new AdHocCommand("create table init_done (initialized_on date);");
      $db->Execute($command);
    }
    catch(Exception $e)
    {
      Log::Debug("init.php:initdb: failed to create table, db has been initialized.");
      Log::Debug($e->getMessage());
      $initialized = true;
    }

    if (!$initialized)
    {
      try
      {
        Log::Debug("init.php:initdb: db not initialized, initialize now.");

        $dbUser = Configuration::Instance()->GetSectionKey(ConfigSection::DATABASE, ConfigKeys::DATABASE_USER);
        $dbPassword = Configuration::Instance()->GetSectionKey(ConfigSection::DATABASE, ConfigKeys::DATABASE_PASSWORD);
        $dbHostSpec = Configuration::Instance()->GetSectionKey(ConfigSection::DATABASE, ConfigKeys::DATABASE_HOSTSPEC);
        $dbName = Configuration::Instance()->GetSectionKey(ConfigSection::DATABASE, ConfigKeys::DATABASE_NAME);

        // run sql scripts from the Booked distribution to create database schema
        $command = 'mysql'
        . ' --host=' . $dbHostSpec
        . ' --user=' . $dbUser
        . ' --password=' . $dbPassword
        . ' --database=' . $dbName
        . ' --execute="SOURCE ';
        $output1 = shell_exec($command . '/var/www/html/database_schema/create-schema.sql"');
        $output2 = shell_exec($command . '/var/www/html/database_schema/create-data.sql"');

        // run other init functions
        // addAttributes();
        // createAdminGroup();
        // $scheduleId = createSchedule();
        // createResources($scheduleId);

      }
      catch(Exception $e)
      {
        Log::Debug("init.php:initdb: error in initialization.");
        Log::Debug($e->getMessage());
      }
    }
  }
}

initdb();

?>
