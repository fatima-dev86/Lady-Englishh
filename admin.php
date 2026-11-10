
<?php
// Lady English owner page: special offers + private messages inbox.
require __DIR__ . '/lib.php';

header('X-Robots-Tag: noindex, nofollow');
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax'
]);

// Safely read the saved admin password.
$authFile = DATA_DIR . '/admin.php';
$hash = null;

if (is_file($authFile)) {
    $a = include $authFile;
    $hash = is_array($a) ? ($a['hash'] ?? null) : null;
}

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$msg = '';
$err = '';

// Read saved messages.
function admin_read_messages(): array {
    $file = DATA_DIR . '/messages.json';

    if (!is_file($file)) {
        return [];
    }

    $handle = @fopen($file, 'rb');
    if (!$handle) {
        return [];
    }

    flock($handle, LOCK_SH);
    $content = stream_get_contents($handle);
    flock($handle, LOCK_UN);
    fclose($handle);

    $messages = json_decode((string)$content, true);

    return is_array($messages) ? $messages : [];
}

// Update a message safely while preventing simultaneous file writes.
function admin_update_message(string $id, string $action): bool {
    if (!in_array($action, ['read', 'delete'], true)) {
        return false;
    }

    ensure_data_dir();

    $file = DATA_DIR . '/messages.json';
    $handle = @fopen($file, 'c+');

    if (!$handle) {
        return false;
    }

    if (!flock($handle, LOCK_EX)) {
        fclose($handle);
        return false;
    }

    rewind($handle);
    $content = stream_get_contents($handle);
    $messages = json_decode((string)$content, true);

    if (!is_array($messages)) {
        flock($handle, LOCK_UN);
        fclose($handle);
        return false;
    }

    $found = false;
    $updated = [];

    foreach ($messages as $message) {
        if (!is_array($message)) {
            continue;
        }

        if (($message['id'] ?? '') === $id) {
            $found = true;

            if ($action === 'read') {
                $message['read'] = true;
                $updated[] = $message;
            }

            // For delete, do not add the message back.
        } else {
            $updated[] = $message;
        }
    }

    if (!$found) {
        flock($handle, LOCK_UN);
        fclose($handle);
        return false;
    }

    $json = json_encode(
        array_values($updated),
        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
    );

    if ($json === false) {
        flock($handle, LOCK_UN);
        fclose($handle);
        return false;
    }

    rewind($handle);
    $written = ftruncate($handle, 0)
        && fwrite($handle, $json) !== false
        && fflush($handle);

    flock($handle, LOCK_UN);
    fclose($handle);

    return $written;
}

// Handle submitted actions.
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $act = (string)($_POST['action'] ?? '');

    if (
        !isset($_POST['csrf']) ||
        !hash_equals($_SESSION['csrf'], (string)$_POST['csrf'])
    ) {
        $err = 'The page expired. Please try again.';
    }

    // First-time password setup.
    elseif ($act === 'setup' && !$hash) {
        $p = (string)($_POST['password'] ?? '');

        if (strlen($p) < 8) {
            $err = 'Please use at least 8 characters.';
        } else {
            ensure_data_dir();

            $newHash = password_hash($p, PASSWORD_DEFAULT);

            $code = "<?php\nreturn " .
                var_export(['hash' => $newHash], true) . ";\n";

            if (@file_put_contents($authFile, $code, LOCK_EX) === false) {
                $err = 'Could not save the password. Make sure the data folder is writable.';
            } else {
                $hash = $newHash;
                $_SESSION['in'] = true;
                session_regenerate_id(true);
                $msg = 'Password created. You are signed in.';
            }
        }
    }

    // Login.
    elseif ($act === 'login' && $hash) {
        if (password_verify((string)($_POST['password'] ?? ''), $hash)) {
            session_regenerate_id(true);
            $_SESSION['in'] = true;
        } else {
            sleep(1);
            $err = 'Wrong password.';
        }
    }

    // Logout.
    elseif ($act === 'logout') {
        $_SESSION = [];
        session_destroy();
        header('Location: admin.php');
        exit;
    }

    // Save special offer settings.
    elseif ($act === 'save' && !empty($_SESSION['in'])) {
        $on = (($_POST['offer'] ?? '') === 'on');
        $pct = (int)($_POST['percent'] ?? 30);

        if ($pct < 1 || $pct > 90) {
            $err = 'The percentage must be between 1 and 90.';
        } else {
            $msg = save_settings($on, $pct)
                ? 'Saved. The website now shows the new prices.'
                : 'Could not save. Make sure the data folder is writable.';
        }
    }

    // Mark a message as read.
    elseif ($act === 'mark_read' && !empty($_SESSION['in'])) {
        $id = (string)($_POST['id'] ?? '');

        if (admin_update_message($id, 'read')) {
            $msg = 'Message marked as read.';
        } else {
            $err = 'Could not update this message.';
        }
    }

    // Delete a message.
    elseif ($act === 'delete_message' && !empty($_SESSION['in'])) {
        $id = (string)($_POST['id'] ?? '');

        if (admin_update_message($id, 'delete')) {
            $msg = 'Message deleted.';
        } else {
            $err = 'Could not delete this message.';
        }
    }
}

$in = !empty($_SESSION['in']);
$S = settings();

$messages = $in ? admin_read_messages() : [];

$unreadCount = count(array_filter(
    $messages,
    fn($message) => is_array($message) && empty($message['read'])
));

?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Lady English – Owner Dashboard</title>

<style>
*{box-sizing:border-box}

body{
    margin:0;
    font-family:system-ui,-apple-system,"Segoe UI",Arial,sans-serif;
    background:#F2E6D0;
    color:#3A2E1E;
    line-height:1.6;
}

.container{
    max-width:850px;
    margin:35px auto;
    padding:0 16px;
}

.box{
    background:#FFFDF8;
    border:1px solid #DCC9A5;
    border-radius:22px;
    padding:25px;
    margin-bottom:22px;
    box-shadow:7px 7px 0 #F5C431;
}

h1{font-size:1.7rem;margin:0 0 5px}
h2{font-size:1.3rem;margin:0 0 14px}
p{margin:.4rem 0 1rem}

label{
    display:block;
    font-weight:700;
    margin:14px 0 6px;
}

input[type=password],
input[type=number]{
    width:100%;
    padding:12px;
    border:1px solid #DCC9A5;
    border-radius:12px;
    font:inherit;
}

.row{
    display:flex;
    gap:18px;
    flex-wrap:wrap;
}

.row label{
    font-weight:600;
    display:flex;
    gap:8px;
    align-items:center;
    margin:6px 0;
}

button{
    margin-top:14px;
    padding:11px 22px;
    border:0;
    border-radius:999px;
    background:#F5C431;
    color:#3A2E1E;
    font:inherit;
    font-weight:700;
    cursor:pointer;
}

button:hover{opacity:.85}

button.link{
    background:none;
    text-decoration:underline;
    padding:0;
    margin:0;
}

button.delete{
    background:#8B3434;
    color:white;
}

button.small{
    padding:8px 15px;
    font-size:.9rem;
}

.ok{
    background:#F5C431;
    padding:10px 14px;
    border-radius:12px;
    font-weight:700;
}

.bad{
    border:2px solid #D9A410;
    padding:10px 14px;
    border-radius:12px;
    font-weight:700;
}

a{color:inherit}

.dashboard-nav{
    display:flex;
    gap:12px;
    flex-wrap:wrap;
    margin:18px 0;
}

.dashboard-nav a{
    text-decoration:none;
    background:#F2E6D0;
    border:1px solid #DCC9A5;
    padding:9px 15px;
    border-radius:999px;
    font-weight:700;
}

.dashboard-nav a:hover{
    background:#F5C431;
}

.count{
    display:inline-block;
    background:#F5C431;
    padding:2px 10px;
    border-radius:999px;
    font-size:.85rem;
    margin-left:5px;
}

.message-card{
    background:#FFFDF8;
    border:1px solid #DCC9A5;
    border-radius:16px;
    padding:17px;
    margin:14px 0;
    overflow-wrap:anywhere;
}

.message-card.unread{
    border:2px solid #D9A410;
    background:#FFFAE9;
}

.message-top{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
}

.message-status{
    font-size:.8rem;
    font-weight:700;
    padding:3px 10px;
    border-radius:999px;
    background:#E8E3D9;
}

.message-card.unread .message-status{
    background:#F5C431;
}

.message-meta{
    font-size:.9rem;
    color:#776A57;
    margin:5px 0 12px;
}

.message-content{
    white-space:pre-wrap;
    overflow-wrap:anywhere;
    background:#F8F1E5;
    border-radius:12px;
    padding:13px;
}

.message-actions{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    align-items:center;
}

.message-actions form{margin:0}

.empty{
    text-align:center;
    padding:28px 15px;
    background:#F8F1E5;
    border-radius:14px;
}

@media(max-width:550px){
    .box{padding:19px}
    .container{margin:20px auto}
}
</style>
</head>

<body>
<div class="container">

<?php if ($msg): ?>
<p class="ok"><?= h($msg) ?></p>
<?php endif; ?>

<?php if ($err): ?>
<p class="bad"><?= h($err) ?></p>
<?php endif; ?>

<?php if (!$hash): ?>

<div class="box">
    <h1>Lady English – Owner page</h1>
    <p>First time here. Choose a password for this page (at least 8 characters).</p>

    <form method="post">
        <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
        <input type="hidden" name="action" value="setup">

        <label for="p">New password</label>
        <input id="p" type="password" name="password"
               minlength="8" required autocomplete="new-password">

        <button type="submit">Create password</button>
    </form>
</div>

<?php elseif (!$in): ?>

<div class="box">
    <h1>Lady English – Owner page</h1>
    <p>Please sign in to manage your website.</p>

    <form method="post">
        <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
        <input type="hidden" name="action" value="login">

        <label for="p">Password</label>
        <input id="p" type="password" name="password"
               required autocomplete="current-password">

        <button type="submit">Sign in</button>
    </form>
</div>

<?php else: ?>

<div class="box">
    <h1>Lady English – Owner Dashboard</h1>
    <p>Welcome! Manage your special offers and website messages here.</p>

    <nav class="dashboard-nav">
        <a href="#offers">Special Offers</a>
        <a href="#inbox">
            Messages / Inbox
            <?php if ($unreadCount > 0): ?>
                <span class="count"><?= $unreadCount ?></span>
            <?php endif; ?>
        </a>
    </nav>

    <form method="post">
        <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
        <input type="hidden" name="action" value="logout">
        <button class="link" type="submit">Sign out</button>
    </form>
</div>

<!-- SPECIAL OFFERS -->
<section class="box" id="offers">
    <h2>Special Offer Settings</h2>
    <p>Control the special offer. The website changes right after you press Save.</p>

    <form method="post">
        <input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>">
        <input type="hidden" name="action" value="save">

        <label>Special offer</label>

        <div class="row">
            <label>
                <input type="radio" name="offer" value="on"
                    <?= $S['offerActive'] ? 'checked' : '' ?>>
                ON (discounted prices)
            </label>

            <label>
                <input type="radio" name="offer" value="off"
                    <?= !$S['offerActive'] ? 'checked' : '' ?>>
                OFF (original prices)
            </label>
        </div>

        <label for="pc">Discount percentage</label>
        <input id="pc" type="number" name="percent"
               min="1" max="90"
               value="<?= (int)$S['offerPercent'] ?>" required>

        <button type="submit">Save</button>
    </form>

    <p style="margin-top:22px">
        <a href="index.php#courses" target="_blank" rel="noopener">
            See the courses on the website
        </a>
    </p>
</section>

<!-- PRIVATE MESSAGE INBOX -->
<section class="box" id="inbox">
    <h2>
        Messages / Inbox
        <span class="count"><?= count($messages) ?></span>
    </h2>

    <p>
        You have <?= $unreadCount ?> unread
        <?= $unreadCount === 1 ? 'message' : 'messages' ?>.
    </p>

    <?php if (empty($messages)): ?>

        <div class="empty">
            <p><strong>Your inbox is empty.</strong></p>
            <p>Messages submitted through the website contact form will appear here.</p>
        </div>

    <?php else: ?>

        <?php foreach (array_reverse($messages) as $message): ?>
            <?php if (!is_array($message)) continue; ?>

            <article class="message-card <?= empty($message['read']) ? 'unread' : '' ?>">

                <div class="message-top">
                    <strong><?= h($message['name'] ?? 'Unknown sender') ?></strong>

                    <span class="message-status">
                        <?= empty($message['read']) ? 'Unread' : 'Read' ?>
                    </span>
                </div>

                <div class="message-meta">
                    <?php if (!empty($message['email'])): ?>
                        Email:
                        <a href="mailto:<?= h($message['email']) ?>">
                            <?= h($message['email']) ?>
                        </a>
                        <br>
                    <?php else: ?>
                        No email provided<br>
                    <?php endif; ?>

                    Date: <?= h($message['date'] ?? 'Unknown date') ?>
                </div>

                <div class="message-content"><?= h($message['message'] ?? '') ?></div>

                <div class="message-actions">

                    <?php if (empty($message['read']) && !empty($message['id'])): ?>
                        <form method="post">
                            <input type="hidden" name="csrf"
                                   value="<?= h($_SESSION['csrf']) ?>">
                            <input type="hidden" name="action" value="mark_read">
                            <input type="hidden" name="id"
                                   value="<?= h($message['id']) ?>">

                            <button class="small" type="submit">
                                Mark as Read
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if (!empty($message['id'])): ?>
                        <form method="post"
                              onsubmit="return confirm('Are you sure you want to delete this message?');">
                            <input type="hidden" name="csrf"
                                   value="<?= h($_SESSION['csrf']) ?>">
                            <input type="hidden" name="action"
                                   value="delete_message">
                            <input type="hidden" name="id"
                                   value="<?= h($message['id']) ?>">

                            <button class="delete small" type="submit">
                                Delete
                            </button>
                        </form>
                    <?php endif; ?>

                </div>
            </article>

        <?php endforeach; ?>

    <?php endif; ?>
</section>

<?php endif; ?>

</div>
</body>
</html>
