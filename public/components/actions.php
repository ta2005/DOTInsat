<?php if (!empty($config['actions'])): ?>
<div class="row row-actions" style="display: flex; flex-direction: column; gap: 10px;">
    <?php foreach ($config['actions'] as $action): ?>
    <a href="<?= htmlspecialchars($action['href'] ?? '#') ?>" class="action-card" style="width: 100%; justify-content: center; background: #007bff; color: white;">
        <i class="ti <?= htmlspecialchars($action['icon'] ?? '') ?>" aria-hidden="true"></i>
        <span class="action-label"><?= htmlspecialchars($action['label'] ?? '') ?></span>
    </a>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="row row-actions" style="display: flex; flex-direction: column; gap: 10px; margin-top: 20px;">
    <?php foreach ($config['stats'] as $stat): ?>
    <div class="card stat-card" style="display: flex; justify-content: space-between; align-items: center; padding: 15px;">
        <div class="stat-label">
            <?php if (!empty($stat['icon'])): ?>
                <i class="ti <?= htmlspecialchars($stat['icon']) ?> stat-icon" aria-hidden="true"></i>
            <?php endif; ?>
            <?= htmlspecialchars($stat['label']) ?>
        </div>
        <div class="stat-num" style="font-weight: bold; font-size: 1.2rem;">
            <?= htmlspecialchars($stat['value'] ?? '') ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<a href="notifications.php" class="notif-row" style="margin-top: 20px;">
    <div class="notif-left">
        <div class="notif-icon">
            <i class="ti ti-bell" aria-hidden="true"></i>
        </div>
        <div>
            <div class="notif-text">Notifications</div>
            <div class="notif-sub">2 nouvelles réponses</div>
        </div>
    </div>
    <div class="notif-arrow">
        <i class="ti ti-chevron-right" aria-hidden="true"></i>
    </div>
</a>
