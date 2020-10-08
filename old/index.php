<?php

require_once('lib/Validator.php');
require_once('lib/function.php');
require_once('lib/Paginator.php');
require_once('lib/PostsTable.php');
require_once('lib/UploadedFile.php');
require_once('config/init.php');

$message        = null;
$title          = null;
$input_keys     = ['title', 'message', 'password'];
$error_messages = [];

try {
    $posts_table = new PostsTable();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $inputs     = get_inputs($input_keys);
        $image_file = get_file('image');

        $validator      = new Validator($posts_table->getValidateRules());
        $error_messages = $validator->validate(array_merge($inputs, ['image' => $image_file]));

        if (count($error_messages) === 0) {
            if (!is_empty($image_file)) {
                $uploaded_image_file  = new UploadedFile(IMAGE_FILE_DIR_PATH);
                $inputs['image_path'] = $uploaded_image_file->save($image_file);
            }

            $posts_table->insert($inputs);
            header('Location: ' . $_SERVER['SCRIPT_NAME']);
            exit;
        } else {
            $message = $inputs['message'];
            $title   = $inputs['title'];
        }
    }

    if (isset($_GET['current_page'])) {
        $current_page = (int) $_GET['current_page'];
    } else {
        $current_page = 1;
    }

    $posts_count = $posts_table->count();

    $paginator = new Paginator($posts_count);
    $paginator->setCurrentPage($current_page);
    $current_page    = $paginator->getCurrentPage();
    $paginator_items = $paginator->getPaginatorItems($_SERVER['SCRIPT_NAME']);

    $posts_list = $posts_table->select(null, [], 'created_at DESC', $paginator->getItemsNumberPerPage(), $paginator->getOffset())->fetchAll();
} catch (PDOException $e) {
    die('接続エラー: ' . $e->getMessage());
}

?>

<html>
  <head>
    <title>Bulletin board Level 4</title>
    <link rel="stylesheet" href="css/stylesheet.css">
  </head>
  <body>
    <?php if (count($error_messages) !== 0) :?>
      <?php foreach ($error_messages as $error_message) :?>
        <p class="error_message"><?php echo h($error_message) ?></p>
      <?php endforeach ?>
    <?php endif ?>
    <div class="input_area">
      <form action="index.php" method="post" enctype="multipart/form-data">
        <p>Title</p>
        <input name="title" type="text" id="title" value="<?php echo h($title) ?>"/>
        <p>Body</p>
        <textarea name="message"><?php echo h($message) ?></textarea><br><br>
        <p>Password</p>
        <input type="password" name="password" />
        <p>Insert image</p>
        <input type="file" name="image" >
        <div class="submit_area">
          <input type="submit" value="Submit" id="submit_button" />
        </div>
      </form>
    </div>
    <?php foreach ($posts_list as $post) :?>
      <div class="messages">
        <p class="title"><?php echo h($post['title']) ?></p>
        <p class="message"><?php echo nl2br(h($post['message'])) ?></p>
        <?php if (!is_empty($post['image_path'])) :?>
            <img src="<?php echo h($post['image_path']) ?>" width="100" height="80">
        <?php endif ?>
        <form method="post">
          Pass <input type="password" name="delete_password" >
          <input type="hidden" name="post_id" value="<?php echo $post['id'] ?>">
          <input type="hidden" name="current_page" value="<?php echo $current_page ?>">
          <input type="submit" value="Del" formaction="delete.php" id="delete_button">
          <input type="submit" value="Edit" formaction="edit.php">
        </form>
        <p class="created_at"><?php echo h($post['created_at']) ?></p>
      </div>
    <?php endforeach ?>
    <?php foreach ($paginator_items as $paginator_item) : ?>
      <?php if (isset($paginator_item['label']) && isset($paginator_item['current_page'])) : ?>
        <?php if ($paginator_item['current_page'] === $current_page) : ?>
          <div class="stay_page">
            <a><?php echo $paginator_item['label'] ?></a>
          </div>
        <?php else : ?>
          <div class="move_page">
            <a href="<?php echo $paginator_item['url'] ?>"><?php echo $paginator_item['label'] ?></a>
          </div>
        <?php endif ?>
      <?php endif ?>
    <?php endforeach ?>
  </body>
</html>
