<?php
namespace Drupal\keyword_stats\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;

class KeywordStatsTable extends ControllerBase {

  public function table(Request $request) {
    $selectedFilter = \Drupal::request()->query->get('filter');
    $order = \Drupal::request()->query->get('order') ? \Drupal::request()->query->get('order') : 'count';
    $sort = \Drupal::request()->query->get('sort') ? \Drupal::request()->query->get('sort') : 'desc';

    $connection = \Drupal::database();
    $query = $connection->select('keyword_stats', 'ks')
      ->fields('ks', ['keyword', 'count', 'view'])
      ->orderBy($order, $sort);
      
    if(isset($selectedFilter) && !empty($selectedFilter)) {
        $query->condition('view', $selectedFilter);
    }

    $pager = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(10);
    $results = $pager->execute()->fetchAll();
    $getActiveViews = self::getActiveViews();

    $rows = [];
    foreach ($results as $result) {
      $rows[] = [
        'data' => [
          'keyword' => $result->keyword,
          'count' => $result->count,
          'view' => $result->view,
        ],
      ];
    }

    $header = [
      'keyword' => [
        'data' => t('Keyword'),
        'class' => ['sortable'],
        'data-sort-field' => 'keyword',
        'data-sort-order' => $order === 'keyword' ? $sort : 'asc',
      ],
      'count' => [
        'data' => t('Count'),
        'class' => ['sortable'],
        'data-sort-field' => 'count',
        'data-sort-order' => $order === 'count' ? $sort : 'asc',
      ],
      'view' => [
        'data' => t('View'),
        'class' => ['sortable'],
        'data-sort-field' => 'view',
        'data-sort-order' => $order === 'view' ? $sort : 'asc',
      ],
    ];

    $build['form'] = [
      '#type' => 'form',
      '#method' => 'get',
      '#attributes' => ['id' => 'view-filter-form'],
      'view_filter' => [
        '#type' => 'select',
        '#title' => t('Select View'),
        '#options' => ['' => t('All Views')] + $getActiveViews,
        '#attributes' => ['class' => ['filter-options']],            
      ],
      'submit' => [
        '#type' => 'submit',
        '#value' => t('submit'),
        '#attributes' => ['class' => ['filter-btn']]
      ],
    ];

    $build['table'] = [
      '#type' => 'table',
      '#header' => $header,
      '#rows' => $rows,
      '#empty' => t('No content has been found.'),
      '#attributes' => ['id' => 'keyword-stats-table'],
    ];

    $build['pager'] = [
      '#type' => 'pager',
    ];

    return $build;
  }

  public function getActiveViews() {
    $connection = \Drupal::database();
    $query = $connection->select('keyword_stats', 'ks')
        ->fields('ks', ['view']);
    $results = $query->distinct()->execute()->fetchAllKeyed(0,0);
    return $results;
  }
}
