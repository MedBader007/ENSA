```php
<?php
require_once '../includes/session.php';

// Déconnexion
logout();

// Redirection vers la page d'accueil
header('Location: /');
exit();
```