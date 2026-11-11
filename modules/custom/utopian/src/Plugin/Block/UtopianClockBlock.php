<?php

declare(strict_types=1);

namespace Drupal\utopian\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\utopian\UtopianCalendar;

/**
 * Provides a live clock showing the current Utopian datetime.
 */
#[Block(
  id: 'utopian_clock',
  admin_label: new TranslatableMarkup('Mars clock (Utopian datetime)'),
  category: new TranslatableMarkup('Utopian Calendar'),
)]
final class UtopianClockBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    return [
      '#theme' => 'utopian_clock',
      '#mars_time_zones' => UtopianCalendar::TIME_ZONES,
      '#attached' => ['library' => ['utopian/clock']],
    ];
  }

}
