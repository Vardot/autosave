<?php

namespace Drupal\autosave\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

/**
 * Autosave admin settings.
 */
class AutosaveAdminSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {

    return 'autosave_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $config = $this->config('autosave.settings');

    foreach (Element::children($form) as $variable) {
      $config->set($variable, $form_state->getValue($form[$variable]['#parents']));
    }
    $config->save();

    if (method_exists($this, '_submitForm')) {
      $this->_submitForm($form, $form_state);
    }

    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {

    return ['autosave.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['autosave_period'] = [
      '#type' => 'number',
      '#title' => t('Autosave after this amount seconds has passed'),
      '#default_value' => \Drupal::config('autosave.settings')->get('autosave_period'),
      '#min' => 1,
    ];

    $form['autosave_hidden'] = [
      '#prefix' => '<div class="form-item"><label for="edit-autosave-hidden">' . t('Stealth Mode') . '</label>',
      '#type' => 'checkbox',
      '#title' => t('Run in stealth mode'),
      '#description' => t('If this check box is selected no popup will appear notifying user that the form has been autosaved.'),
      '#default_value' => \Drupal::config('autosave.settings')->get('autosave_hidden'),
      '#suffix' => "</div>",
    ];

    return parent::buildForm($form, $form_state);
  }

}
