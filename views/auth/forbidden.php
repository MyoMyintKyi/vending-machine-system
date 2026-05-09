<section class="page-header ">
    <div class="text-center">
        <h1><?= htmlspecialchars((string) ($title ?? 'Access Denied'), ENT_QUOTES, 'UTF-8') ?></h1>
        <div class="text-center"><?= htmlspecialchars((string) ($message ?? 'You are not allowed to access this page.'), ENT_QUOTES, 'UTF-8') ?></div>
    </div>
</section>