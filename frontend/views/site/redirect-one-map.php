<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
  </head>
  <body>
    <a id="my-link" href="<?php echo $link; ?>">Redirecting to OneMap.</a>

    <script>
      window.onload = function() {
        var link = document.getElementById('my-link');
        //link.click();
      }
      </script>
  </body>
</html>