<?php

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Drupal\Core\Database\Database;
use Drupal\taxonomy\Entity\Term;
use Drupal\node\Entity\Node;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends RawDrupalContext implements SnippetAcceptingContext {

  /**
   * Initializes context.
   *
   * Every scenario gets its own context instance.
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct() {
    // Uninstall modules.
    $module_list = array(
      'grundsalg_setup',
      'grundsalg_db_client'
    );

    \Drupal::service('module_installer')->install($module_list);
  }

  /**
   * @Given i/I connect to the test database
   */
  public function useTestDatabase() {
    Database::addConnectionInfo('test', 'default', array(
      'database' => 'dbtest',
      'username' => 'root',
      'password' => 'vagrant',
      'host' => 'localhost',
      'port' => '',
      'driver' => 'mysql',
      'prefix' => '',
    ));

    Database::setActiveConnection('test');
  }

  /**
   * @Given I have a clean database
   */
  public function cleanDatabase() {
    $types = ['subdivision', 'area', 'overview_page'];

    foreach ($types as $type) {
      $query = \Drupal::entityQuery('node')
        ->condition('type', $type);
      $nids = $query->execute();

      $entities = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($nids);

      foreach ($entities as $entity) {
        $entity->delete();
      }
    }

    $types = ['cities', 'plot_type'];

    foreach ($types as $type) {
      $query = \Drupal::entityQuery('taxonomy_term')
        ->condition('vid', $type);
      $nids = $query->execute();

      $entities = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadMultiple($nids);

      foreach ($entities as $entity) {
        $entity->delete();
      }
    }
  }

  /**
   * @Given I have test data
   */
  public function testData1() {
    // Create plot_types and overview_pages
    $plot_types = [
      [
        'name' => 'Villagrund'
      ],
      [
        'name' => 'Storparcel'
      ],
      [
        'name' => 'Erhvervsgrund'
      ],
    ];
    $overview_pages = [];
    foreach ($plot_types as &$plot_type) {
      $plotTerm = Term::create([
        'name' => $plot_type['name'],
        'vid' => 'plot_type',
      ]);

      $plotTerm->save();

      $plot_type['term'] = $plotTerm->id();

      // Create overview_pages
      $overview_page = Node::create([
        'type'        => 'overview_page',
        'title'       =>  $plot_type['name'],
        'field_plot_type' => $plotTerm,
      ]);

      $overview_page->save();

      $overview_pages[] = $overview_page;
    }

    // Create cities and areas.
    $cities = [
      [
        'postal' => 8200,
        'name' => 'TestBy1'
      ],
      [
        'postal' => 8500,
        'name' => 'TestBy2'
      ],
    ];
    foreach ($cities as $city) {
      $cityTerm = Term::create([
        'name' => $city['name'],
        'vid' => 'cities',
        'field_postalcode' => $city['postal'],
      ]);

      $cityTerm->save();

      $area = Node::create([
        'type' => 'area',
        'title' => $city['name'],
        'field_parent' => $overview_pages[0]->id(),
        'field_plot_type' => $plot_types[0]['term'],
        'field_city_reference' => $cityTerm->id(),
      ]);

      $area->save();
     }
  }

  /**
   * Creates events
   *
   * @Given i/I have called updateSubdivision with subdivisionId :subdivisionId subdivisionName :subdivisionName type :type postal code :postalCode city name :cityName
   */
  public function updateSubdivision($subdivisionId, $subdivisionName, $type, $postalCode, $cityName) {
    $contentCreation = \Drupal::service('grundsalg.content_creation');

    $contentCreation->updateSubdivision($subdivisionId, $subdivisionName, $type, $postalCode, $cityName);
  }

  /**
   * Creates events
   *
   * @Then i/I should be able to find the :termType term with name :name
   */
  public function checkTerm($termType, $name) {
    // Make sure an overview with the $type exists.
    $query = \Drupal::entityQuery('taxonomy_term')
      ->condition('vid', $termType)
      ->condition('name', $name);
    $nids = $query->execute();

    return count($nids) == 1;
  }
}
