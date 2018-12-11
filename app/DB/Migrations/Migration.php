<?php
/**
 * Created by PhpStorm.
 * User: kamil
 * Date: 2018-12-07
 * Time: 14:00
 */

namespace App\DB\Migrations;

use Illuminate\Database\Capsule\Manager as Capsule;
use Phinx\Migration\AbstractMigration;

class Migration extends AbstractMigration {
    /** @var \Illuminate\Database\Capsule\Manager $capsule */
    public $capsule;
    /** @var \Illuminate\Database\Schema\Builder $capsule */
    public $schema;

    public function init()  {
        $this->capsule = new Capsule;
        $this->capsule->addConnection([
            'driver'      =>  $_ENV[DB_CONNECTION],
            'host'        =>  $_ENV[DB_HOST],
            'database'    =>  $_ENV[DB_DATABASE],
            'username'    =>  $_ENV[DB_USERNAME],
            'password'    =>  $_ENV[DB_PASSWORD],
            'charset'     => 'utf8',
            'collation'   => 'utf8_unicode_ci',
        ]);

        $this->capsule->bootEloquent();
        $this->capsule->setAsGlobal();
        $this->schema = $this->capsule->schema();
    }
}