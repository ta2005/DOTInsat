<?php 
// boutonet , graphe stat mtaa ll prof 
?>
<head>
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/forms.css">
    <link rel="stylesheet" href="/css/notifications.css">
</head>
<!-- STAT CARDS -->
<?php
$hasBigStats  = !empty($config['stats']) && !empty($config['stats'][0]['big']);
$isAdmin      = ($config['role'] ?? '') === 'Administrateur';
$rowClass     = $hasBigStats ? 'row-stats-big' : 'row-actions';
?>
<div class="row <?= $rowClass ?>">
    <?php foreach ($config['stats'] as $stat):
        $hasBig   = !empty($stat['big']);
        $hasLabel = array_key_exists('label', $stat) && !$hasBig;
    ?>
    <div class="card stat-card <?= $hasBig ? 'stat-card--big' : '' ?>">

        <?php if ($hasBig): ?>
            <div class="stat-num-big">
                <?= htmlspecialchars($stat['value'] ?? '') ?>
                <?php if (!empty($stat['total'])): ?>
                    <span class="total-divider">/<?= htmlspecialchars($stat['total']) ?></span>
                <?php endif; ?>
            </div>
            <div class="stat-label-bottom"><?= htmlspecialchars($stat['label'] ?? '') ?></div>

        <?php elseif ($hasLabel): ?>
            <div class="stat-label"><?= htmlspecialchars($stat['label']) ?></div>
            <div class="stat-num">
                <?php if (!empty($stat['icon'])): ?>
                    <i class="ti <?= htmlspecialchars($stat['icon']) ?> stat-icon" aria-hidden="true"></i>
                <?php endif; ?>
                <?= htmlspecialchars($stat['value'] ?? '') ?>
                <?php if (!empty($stat['total'])): ?>
                    <span class="total-divider">/<?= htmlspecialchars($stat['total']) ?></span>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <div class="stat-sub"><?= htmlspecialchars($stat['sub'] ?? '') ?></div>
            <div class="stat-val <?= htmlspecialchars($stat['color'] ?? '') ?>">
                <?php if (!empty($stat['icon'])): ?>
                    <i class="ti <?= htmlspecialchars($stat['icon']) ?> val-icon" aria-hidden="true"></i>
                <?php endif; ?>
                <?= htmlspecialchars($stat['value'] ?? '') ?>
            </div>

        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<!-- GRAPHE (uniquement si défini dans $config) -->
<?php if (!empty($config['chart'])): ?>
<div class="row row-full">
    <div class="card chart-placeholder-card">
        <div class="chart-header">
            <h3><?= htmlspecialchars($config['chart']['title']) ?></h3>
            <?php if (!empty($config['chart']['legend'])): ?>
            <div class="chart-legend-mock">
                <?php foreach ($config['chart']['legend'] as $item): ?>
                    <span class="legend-item">
                        <span class="color-indicator <?= htmlspecialchars($item['color']) ?>"></span>
                        <?= htmlspecialchars($item['label']) ?>
                    </span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="chart-body-area">
            <p class="placeholder-text">[ Espace réservé pour l'affichage du graphe ]</p>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ACTION BUTTONS -->
<?php if (!empty($config['actions'])): ?>
<div class="row <?= $isAdmin ? 'row-actions-6' : 'row-actions' ?>">
    <?php foreach ($config['actions'] as $action): ?>
    <a href="<?= htmlspecialchars($action['href'] ?? '#') ?>" class="action-card">
        <i class="ti <?= htmlspecialchars($action['icon'] ?? '') ?>" aria-hidden="true"></i>
        <span class="action-label"><?= htmlspecialchars($action['label'] ?? '') ?></span>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- NOTIFICATION -->
<a href="notifications.php" class="notif-row">
    <div class="notif-left">
        <div class="notif-icon">
            <i class="ti ti-bell" aria-hidden="true"></i>
        </div>
        <div>
            <div class="notif-text">Notification</div>
            <div class="notif-sub">Aucune nouvelle notification</div>
        </div>
    </div>
    <div class="notif-arrow">
        <i class="ti ti-chevron-right" aria-hidden="true"></i>
    </div>
</a>