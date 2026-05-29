<?php require_once BASE_PATH . '/views/layouts/header.php'; ?>
<link rel="stylesheet" href="/css/styles.css">
<link rel="stylesheet" href="/css/forum.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

<div class="forum-layout">

    <!-- ═══ COLONNE GAUCHE : groupes ═════════════════════════════════════════ -->
    <aside class="forum-sidebar">

        <!-- Mes groupes -->
        <div class="forum-card">
            <div class="forum-card-header">
                <i class="ti ti-users-group"></i>
                <span>Mes groupes</span>
            </div>

            <?php if (empty($myGroups)): ?>
                <p class="forum-empty-msg">Vous n'avez rejoint aucun groupe.</p>
            <?php else: ?>
                <ul class="forum-group-list">
                    <?php foreach ($myGroups as $g): ?>
                        <li class="forum-group-item <?= $filterGroupId === (int)$g['id'] ? 'forum-group-item--active' : '' ?>">
                            <a href="/?page=forum&group_id=<?= $g['id'] ?>" class="forum-group-link">
                                <span class="forum-group-avatar">
                                    <?= mb_strtoupper(mb_substr($g['nom'], 0, 2)) ?>
                                </span>
                                <span class="forum-group-name"><?= htmlspecialchars($g['nom']) ?></span>
                            </a>
                            <!-- Quitter -->
                            <form method="POST" action="/?page=leave-group" class="forum-leave-form">
                                <input type="hidden" name="group_id" value="<?= $g['id'] ?>">
                                <button type="submit" class="forum-btn-leave" title="Quitter">
                                    <i class="ti ti-door-exit"></i>
                                </button>
                            </form>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <!-- Voir tous les posts -->
            <?php if ($filterGroupId !== null): ?>
                <a href="/?page=forum" class="forum-link-all">
                    <i class="ti ti-layout-list"></i> Voir tous mes posts
                </a>
            <?php endif; ?>
        </div>

        <!-- Rejoindre un groupe -->
        <?php if (!empty($unjoinedGroups)): ?>
        <div class="forum-card">
            <div class="forum-card-header">
                <i class="ti ti-search"></i>
                <span>Rejoindre un groupe</span>
            </div>
            <ul class="forum-group-list">
                <?php foreach ($unjoinedGroups as $g): ?>
                    <li class="forum-group-item">
                        <span class="forum-group-avatar forum-group-avatar--gray">
                            <?= mb_strtoupper(mb_substr($g['nom'], 0, 2)) ?>
                        </span>
                        <span class="forum-group-name"><?= htmlspecialchars($g['nom']) ?></span>
                        <form method="POST" action="/?page=join-group" class="forum-join-form">
                            <input type="hidden" name="group_id" value="<?= $g['id'] ?>">
                            <button type="submit" class="forum-btn-join" title="Rejoindre">
                                <i class="ti ti-plus"></i>
                            </button>
                        </form>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

    </aside>

    <!-- ═══ COLONNE CENTRALE : feed ══════════════════════════════════════════ -->
    <main class="forum-main">

        <!-- Flash message -->
        <?php if ($flash): ?>
            <div class="flash flash--<?= $flash['type'] ?>">
                <i class="ti <?= $flash['type'] === 'success' ? 'ti-circle-check' : 'ti-circle-x' ?>"></i>
                <?= htmlspecialchars($flash['msg']) ?>
            </div>
        <?php endif; ?>

        <!-- Composer un post -->
        <?php if (!empty($myGroups)): ?>
        <div class="forum-card forum-compose">
            <div class="forum-compose-avatar">
                <?= mb_strtoupper(mb_substr($_SESSION['user_prenom'] ?? 'U', 0, 1) . mb_substr($_SESSION['user_nom'] ?? '', 0, 1)) ?>
            </div>
            <form method="POST" action="/?page=save-post" class="forum-compose-form">
                <textarea
                    name="contenu"
                    class="forum-compose-textarea"
                    rows="3"
                    placeholder="Exprimez-vous..."
                    required
                ></textarea>
                <div class="forum-compose-footer">
                    <select name="groupe_id" class="forum-select" required>
                        <option value="">— Choisir un groupe —</option>
                        <?php foreach ($myGroups as $g): ?>
                            <option
                                value="<?= $g['id'] ?>"
                                <?= $filterGroupId === (int)$g['id'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($g['nom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="forum-btn-publish">
                        <i class="ti ti-send"></i> Publier
                    </button>
                </div>
            </form>
        </div>
        <?php else: ?>
        <div class="forum-card forum-no-group-banner">
            <i class="ti ti-info-circle"></i>
            Rejoignez un groupe pour pouvoir publier des posts.
        </div>
        <?php endif; ?>

        <!-- En-tête du feed -->
        <div class="forum-feed-header">
            <i class="ti ti-messages"></i>
            <h2>
                <?php if ($filterGroupId !== null): ?>
                    <?php
                        $nomGroupe = '';
                        foreach ($myGroups as $g) {
                            if ((int)$g['id'] === $filterGroupId) {
                                $nomGroupe = $g['nom'];
                                break;
                            }
                        }
                    ?>
                    <?= htmlspecialchars($nomGroupe) ?>
                <?php else: ?>
                    Fil d'actualité
                <?php endif; ?>
            </h2>
        </div>

        <!-- Liste des posts -->
        <?php if (empty($posts)): ?>
            <div class="forum-empty-feed">
                <i class="ti ti-message-off"></i>
                <p>Aucun post à afficher.</p>
                <?php if (empty($myGroups)): ?>
                    <small>Rejoignez un groupe pour voir les posts de ses membres.</small>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="forum-posts">
                <?php foreach ($posts as $post): ?>
                <div class="forum-post-card">

                    <div class="forum-post-header">
                        <!-- Avatar auteur -->
                        <div class="forum-post-avatar">
                            <?= mb_strtoupper(
                                mb_substr($post['prenom'] ?? 'U', 0, 1) .
                                mb_substr($post['nom']    ?? '',  0, 1)
                            ) ?>
                        </div>
                        <div class="forum-post-meta">
                            <span class="forum-post-author">
                                <?= htmlspecialchars(($post['prenom'] ?? '') . ' ' . ($post['nom'] ?? '')) ?>
                            </span>
                            <?php if (!empty($post['groupe_nom'])): ?>
                                <span class="forum-post-group-badge">
                                    <i class="ti ti-users-group"></i>
                                    <?= htmlspecialchars($post['groupe_nom']) ?>
                                </span>
                            <?php endif; ?>
                            <span class="forum-post-date">
                                <?php
                                    $dt = new DateTimeImmutable($post['date_de_creation']);
                                    echo $dt->format('d M Y · H:i');
                                ?>
                            </span>
                        </div>

                        <!-- Bouton supprimer (seulement l'auteur) -->
                        <?php if ((int)$post['auteur_id'] === (int)($_SESSION['user_id'] ?? 0)): ?>
                        <form method="POST" action="/?page=delete-post" class="forum-delete-form">
                            <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                            <button
                                type="submit"
                                class="forum-btn-delete"
                                title="Supprimer"
                                onclick="return confirm('Supprimer ce post ?')"
                            >
                                <i class="ti ti-trash"></i>
                            </button>
                        </form>
                        <?php endif; ?>
                    </div>

                    <div class="forum-post-body">
                        <?= nl2br(htmlspecialchars($post['contenu'])) ?>
                    </div>

                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </main>

</div>
