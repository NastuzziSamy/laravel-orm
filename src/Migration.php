<?php

namespace LaravelORM;

class Migration {
    protected static $path;

    public static function getPath() {
        if (!self::$path) {
            self::$path = base_path('database/migrations');
        }

        return self::$path;
    }

    public static function generateModelMigration($name) {
        $schema = $name::getSchema();
        $dataForMigration = $schema->generateMigration();
        $dataForMigration['name'] = 'Create'.ucfirst($dataForMigration['table']).'Table';

        $rendered = Template::render('migration', $dataForMigration);

        file_put_contents(self::getPath().'/'.$dataForMigration['table'].'.php', $rendered);
    }
}
