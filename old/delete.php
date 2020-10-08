<?php

require_once('lib/function.php');
require_once('lib/PostsTable.php');
require_once('config/init.php');
require_once('lib/UploadedFile.php');

$error_messages = [];
$input_keys     = ['title', 'message', 'delete_password', 'post_id', 'current_page', 'confirm', 'delete_image_path'];
try {
    $inputs = get_inputs($input_keys);

    if (is_empty($inputs['post_id']) || is_empty($inputs['current_page'])) {
        http_response_code(400);
        exit;
    }

    $posts_table = new PostsTable();
    $post        = $posts_table->select('id = :id', ['id' => $inputs['post_id']])->fetch();

    if ($post === false) {
        http_response_code(404);
        exit;
    }

    $is_password_set     = true;
    $is_password_matched = true;

    if (is_empty($post['password'])) {
        $is_password_set = false;
    } elseif (!password_verify($inputs['delete_password'], $post['password'])) {
        $is_password_matched = false;
    }

    if (isset($inputs['confirm']) && count($error_messages) === 0 && $is_password_set && $is_password_matched) {
        $posts_table->delete('id = :id', ['id' => $inputs['post_id']]);
        $uploaded_image_file = new UploadedFile(IMAGE_FILE_DIR_PATH);
        $uploaded_image_file->delete($inputs['delete_image_path']);

        header('Location: index.php?current_page=' . $inputs['current_page']);
        exit;
    }
} catch (PDOException $e) {
    die('接続エラー: '.$e->getMessage());
}

 ?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>投稿削除</title>
    <link rel="stylesheet" href="css/stylesheet.css">
  </head>
  <body>
    <?php if (!is_empty($error_messages)) :?>
      <?php foreach ($error_messages as $error_message) :?>
        <p class="error_message"><?php echo h($error_message) ?></p>
      <?php endforeach ?>
    <?php endif ?>
    <?php if (!$is_password_matched) :?>
      <p class="error_message">This passwords you entered do not match. Please try again.</p>
    <?php elseif (!$is_password_set) :?>
      <p class="error_message">This message cna't edit, because this message has not been set password.</p>
    <?php endif ?>
    <div class="messages">
      <p class="title"><?php echo h($post['title']) ?></p>
      <p class="message"><?php echo h($post['message']) ?></p>
      <p class="created_at"><?php echo h($post['created_at']) ?></p>
      <?php if (!is_empty($post['image_path'])) :?>
        <img src="<?php echo h($post['image_path']) ?>" width="100" height="80">
      <?php endif ?>
    </div>
    <?php if (!$is_password_matched) :?>
      <form action="delete.php" method="post">
        Pass <input type="password" name="delete_password" >
        <input type="hidden" name="post_id" value="<?php echo $post['id'] ?>">
        <input type="hidden" name="current_page" value="<?php echo $inputs['current_page'] ?>">
        <input type="submit" value="Del" id="delete_button">
      </form>
    <?php elseif (!$is_password_set) :?>
      <form action="index.php" method="get">
        <input type="hidden" name="current_page" value="<?php echo $inputs['current_page'] ?>">
        <input type="submit" value="Back previous page">
      </form>
    <?php elseif (is_empty($error_messages)) :?>
      <div class="confirm">
        <p>Are you sure?</p>
        <form action="delete.php" method="post">
          <input type="hidden" name="post_id" value="<?php echo $post['id'] ?>">
          <input type="hidden" name="current_page" value="<?php echo $inputs['current_page'] ?>">
          <input type="hidden" name="delete_password" value="<?php echo $inputs['delete_password'] ?>">
          <input type="hidden" name="delete_image_path" value="<?php echo $post['image_path'] ?>">
          <input type="submit" name="confirm" value="Yes">
        </form>
        <form action="index.php" method="get">
          <input type="hidden" name="current_page" value="<?php echo $inputs['current_page'] ?>">
          <input type="submit" value="Cancel">
        </form>
      </div>
    <?php endif ?>
  </body>
</html>
