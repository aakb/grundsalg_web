#!/usr/bin/env bash

dir=$(cd $(dirname "${BASH_SOURCE[0]}") && pwd)

cd $dir/..

drupal_root=$(drush php-eval 'echo DRUPAL_ROOT')

#TODO: Check that drupal is not empty.

drush --yes site-install itkore --db-url=mysql://root:vagrant@localhost/dbtest --sites-subdir=test --site-name=test --config-dir=/vagrant/htdocs/config/sync
#cd $drupal_root/sites/test
#drush @test --yes config-set "system.site" uuid "49aa6704-d739-4eec-8829-09a11d5f31cd"
#drush @test --yes config-import --source=$drupal_root/../config/sync
cd $dir
$drupal_root/../vendor/bin/behat