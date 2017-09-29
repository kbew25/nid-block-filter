<?php

namespace Drupal\MODULE\Plugin\Condition;

use Drupal\Core\Condition\ConditionInterface;
use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin to select nid.
 *
 * @Condition(
 *   id = "block_nid",
 *   label = @Translation("Node ID"),
 *   context = {
 *     "node" = @ContextDefinition("entity:node", required = FALSE, label = @Translation("Node"))
 *   }
 * )
 */
class Nid extends ConditionPluginBase implements ConditionInterface, ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * Creates a new ExampleCondition instance.
   *
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $nodes = [];
    foreach ($this->configuration['cnid'] as $key => $value) {
      $nodes[] = $value['target_id'];
    }
    $entity = \Drupal::entityTypeManager()->getStorage('node')->loadMultiple($nodes);
    $entity = (!empty($entity)) ? $entity : FALSE;

    $form['cnid'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Nodes'),
      '#default_value' => $entity,
      '#selection_settings' => [
        'target_bundles' => [],
      ],
      '#target_type' => 'node',
      '#tags' => TRUE,
    ];

    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['cnid'] = $form_state->getValue('cnid');
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * Evaluates the condition and returns TRUE or FALSE accordingly.
   *
   * @return bool
   *   TRUE if the condition has been met, FALSE otherwise.
   */
  public function evaluate() {
    if (empty($this->configuration['cnid']) && !$this->isNegated()) {
      return TRUE;
    }

    $nodes = [];
    foreach ($this->configuration['cnid'] as $key => $value) {
      $nodes[] = $value['target_id'];
    }

    $node = $this->getContextValue('node');
    if ($node && in_array($node->id(), $nodes)) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Provides a human readable summary of the condition's configuration.
   */
  public function summary() {
    return $this->t('Selects page by nid to show block.');
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['cnid' => ''] + parent::defaultConfiguration();
  }

}
