/**
 * @file
 * Contains the Video Controller.
 */

angular.module('grundsalg').controller('VideoController', [
    '$scope', '$sce',
    function ($scope, $sce) {
      // Set path from the backend.
      $scope.video = drupalSettings.variables.video;

      // Trust the iframe for youtube.
      $scope.youtube = $sce.trustAsHtml('<iframe src="//www.youtube.com/embed/' + drupalSettings.variables.video + '?rel=0&showinfo=0" frameborder="0" allowfullscreen></iframe>');
    }
  ]
);