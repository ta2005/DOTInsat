<?php 
// boutonet / boutonet ll stat
?>
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/forms.css">

</head>

<!-- boutonet ll stats -->
<?php
// bch nverifyi ken andou stat mtaa big 
$hasBigStats  = !empty($config['stats']) && !empty($config['stats'][0]['big']);
// bch nverifyi ken andou role mtaa admin bch napplyi style mtaa les actions ken houwa admin w ykoun 6 actions sinon 4
$isAdmin      = ($config['role'] ?? '') === 'Administrateur';
// bch napplyi style mtaa les actions ken houwa admin w ykoun 6 actions sinon 4
$rowClass     = $hasBigStats ? 'row-stats-big' : 'row-actions';
?>

<div class="row <?= $rowClass ?>">
    <?php foreach ($config['stats'] as $stat):

        // bch nverifi ken andou stat mtaa big 
        $hasBig   = !empty($stat['big']);
        // bch nverifi ken andou label w houwa mouch stat mtaa big bch napplyi style mtaa les stat ken houwa mouch big w andou label
        $hasLabel = array_key_exists('label', $stat) && !$hasBig;
    ?>
    <!-- lehne fi kol cas nchouf ken aandou value /total/ icone/label -->
    <div class="card stat-card <?= $hasBig ? 'stat-card--big' : '' ?>">
        <!-- ken houwa big napplyi style hedha -->
        <?php if ($hasBig): ?>
            <div class="stat-num-big">
                <?= htmlspecialchars($stat['value'] ?? '') ?>
                <?php if (!empty($stat['total'])): ?>
                    <span class="total-divider">/<?= htmlspecialchars($stat['total']) ?></span>
                <?php endif; ?>
            </div>
            <div class="stat-label-bottom"><?= htmlspecialchars($stat['label'] ?? '') ?></div>
        
        
        <!-- ken houwa mouch big w andou label napplyi style hedha -->
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
        
        
        <!-- ken houwa mouch big w ma aandouch label napplyi style hedha -->
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


<!-- bouton illi fehom actions -->
<?php if (!empty($config['actions'])): ?>
<!-- nchouf ll cas mtaa admin wala le -->
<div class="row <?= $isAdmin ? 'row-actions-6' : 'row-actions' ?>">
    <?php foreach ($config['actions'] as $action): ?>
    <a href="<?= htmlspecialchars($action['href'] ?? '#') ?>" class="action-card">
        <i class="ti <?= htmlspecialchars($action['icon'] ?? '') ?>" aria-hidden="true"></i>
        <span class="action-label"><?= htmlspecialchars($action['label'] ?? '') ?></span>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>