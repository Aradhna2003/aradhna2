<?php
namespace Drupal\keyword_stats\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Database\Database;

class KeywordStatsDBTable extends ControllerBase {
  public function content(Request $request) {
    $json_data = \Drupal::request()->query->all();
    
    if (!empty($json_data) && is_array($json_data) && isset($json_data['titles']) && isset($json_data['viewId'])) {
      $connection = Database::getConnection();
      $titles = $json_data['titles'];
      $viewId = $json_data['viewId'];

      foreach ($titles as $title) {
        $query = $connection->select('keyword_stats', 'ks')
          ->fields('ks', ['keyword', 'count', 'view'])
          ->condition('keyword', $title)
          ->condition('view', $viewId)
          ->execute()
          ->fetchAssoc();

        if ($query) {
          $connection->update('keyword_stats')
            ->fields(['count' => $query['count'] + 1])
            ->condition('keyword', $title)
            ->condition('view', $viewId)
            ->execute();
        } else {
          $connection->insert('keyword_stats')
            ->fields([
              'keyword' => $title,
              'count' => 1,
              'view' => $viewId,
            ])
            ->execute();
        }
      }

      return new JsonResponse(['message' => 'Titles have been successfully saved.']);
    } else {
      return new JsonResponse(['message' => 'No titles received or invalid data.'], 400);
    }
  }
}
