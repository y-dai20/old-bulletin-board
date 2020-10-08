<?php

require_once('lib/Validator.php');
require_once('lib/function.php');
require_once('lib/PostsTable.php');
require_once('lib/UploadedFile.php');
require_once('config/init.php');

$error_messages = [];
$input_keys     = ['title', 'message', 'delete_password', 'post_id', 'current_page', 'edit', 'delete_image_path'];

try {
    $inputs     = get_inputs($input_keys);
    $image_file = get_file('image');

    if (is_empty($inputs['post_id']) || is_empty($inputs['current_page'])) {
        http_response_code(400);
        exit;
    }

    $posts_table = new PostsTable();
    $post        = $posts_table->select('id = :id',['id' => $inputs['post_id']])->fetch();

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

    $title   = $post['title'];
    $message = $post['message'];

    if (!is_empty($inputs['edit'])) {
        $title   = $inputs['title'];
        $message = $inputs['message'];

        $validator      = new Validator($posts_table->getValidateRules());
        $error_messages = $validator->validate(array_merge($inputs, ['image' => $image_file]));

        if (count($error_messages) === 0) {
            $delete_image_path   = $inputs['delete_image_path'];
            $uploaded_image_file = new UploadedFile(IMAGE_FILE_DIR_PATH);

            if (!is_empty($image_file)) {
                $image_path = $uploaded_image_file->save($image_file);
                if (!is_empty($delete_image_path)) {
                    $uploaded_image_file->delete($delete_image_path);
                }
            } elseif (!is_empty($delete_image_path)) {
                $image_path = null;
                $uploaded_image_file->delete($delete_image_path);
            } else {
                $image_path = $post['image_path'];
            }

            $values = [
                'title'      => $inputs['title'],
                'message'    => $inputs['message'],
                'image_path' => $image_path,
            ];

            $posts_table->update($values, 'id = :id',['id' => $inputs['post_id']]);
            header('Location: index.php?current_page=' . $inputs['current_page']);
            exit;
        }
    }
} catch (PDOException $e) {
    die('接続エラー: '.$e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>投稿編集</title>
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
    <?php if (!$is_password_matched || !$is_password_set) :?>
      <div class="messages">
        <p class="title"><?php echo h($post['title']) ?></p>
        <p class="message"><?php echo h($post['message']) ?></p>
        <p class="created_at"><?php echo h($post['created_at']) ?></p>
        <?php if (!is_empty($post['image_path'])) :?>
          <img src="<?php echo h($post['image_path']) ?>" width="60" height="50">
        <?php endif ?>
      </div>
    <?php endif ?>
    <?php if (!$is_password_matched) :?>
      <form action="edit.php" method="post">
        Pass <input type="password" name="delete_password" >
        <input type="hidden" name="post_id" value="<?php echo $post['id'] ?>">
        <input type="hidden" name="current_page" value="<?php echo $inputs['current_page'] ?>">
        <input type="submit" value="Edit" id="edit_button">
      </form>
    <?php elseif (!$is_password_set) :?>
      <form action="index.php" method="get">
        <input type="hidden" name="current_page" value="<?php echo $inputs['current_page'] ?>">
        <input type="submit" value="Back previous page">
      </form>
    <?php else :?>
      <div class="input_area">
        <form action="edit.php" method="post" enctype="multipart/form-data">
          <p>Title</p>
          <input name="title" type="text" id="title" value="<?php echo $title ?>"/>
          <p>Body</p>
          <textarea name="message"><?php echo h($message)?></textarea><br><br>
          <?php if (!is_empty($post['image_path'])) :?>
            <img src="<?php echo h($post['image_path']) ?>" width="100" height="80">
            <input type="checkbox" name="delete_image_path" value="<?php echo $post['image_path'] ?>">Delete image
          <?php endif ?>
          <p>Insert image</p>
          <input type="file" name="image">
          <div class="submit_area">
            <input type="submit" value="Submit" id="submit_button" name="edit"/>
          </div>
          <input type="hidden" name="current_page" value="<?php echo $inputs['current_page'] ?>">
          <input type="hidden" name="post_id" value="<?php echo $inputs['post_id'] ?>">
          <input type="hidden" name="delete_password" value="<?php echo $inputs['delete_password'] ?>">
        </form>
        <form action="index.php" method="get">
          <input type="hidden" name="current_page" value="<?php echo $inputs['current_page'] ?>">
          <input type="submit" value="Cancel">
        </form>
      </div>
    <?php endif ?>
  </body>
</html>
