<?php
// public/components/feed.php

// Fetch all posts from the repository
$posts = $postRepo->fetchAll();
?>

<div class="feed-header" style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
    <i class="ti ti-messages" style="font-size: 24px; color: #3b82f6;"></i>
    <h2 style="font-size: 20px; font-weight: 700;">Fil d'actualité</h2>
</div>

<div class="posts-list" style="display: flex; flex-direction: column; gap: 16px;">
    <?php if (empty($posts)): ?>
        <div class="card" style="padding: 30px; text-align: center; color: var(--text-muted);">
            <i class="ti ti-mood-empty" style="font-size: 40px; margin-bottom: 10px;"></i>
            <p>Aucun post pour le moment. Soyez le premier à publier !</p>
        </div>
    <?php else: ?>
        <?php foreach ($posts as $post): ?>
            <?php 
                // Fetch the author's details
                $author = $userRepo->fetchById($post->getIdUser());
                $authorName = $author ? $author->getNom() . ' ' . $author->getPrenom() : 'Utilisateur Inconnu';
            ?>
            <div class="card" style="padding: 20px; border-left: 4px solid #3b82f6;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div class="admin-avatar" style="width: 36px; height: 36px; border-radius: 50%;">
                            <i class="ti ti-user" style="font-size: 18px;"></i>
                        </div>
                        <div>
                            <div style="font-size: 14px; font-weight: 600; color: var(--text-primary);">
                                <?= htmlspecialchars($authorName) ?>
                            </div>
                            <div style="font-size: 11px; color: var(--text-muted);">
                                <?= $post->getDateDeCreation()->format('d M Y, H:i') ?>
                            </div>
                        </div>
                    </div>
                    <?php if ($post->getIdGroup()): ?>
                        <span class="badge badge--blue">Groupe #<?= $post->getIdGroup() ?></span>
                    <?php endif; ?>
                </div>
                
                <div style="font-size: 14.5px; line-height: 1.6; color: #ccc;">
                    <?= nl2br(htmlspecialchars($post->getContenu())) ?>
                </div>
                
                <div style="margin-top: 16px; padding-top: 12px; border-top: 1px solid var(--border-subtle); display: flex; gap: 16px;">
                    <a href="#" style="color: var(--text-secondary); text-decoration: none; font-size: 13px; display: flex; align-items: center; gap: 4px;">
                        <i class="ti ti-thumb-up"></i> J'aime
                    </a>
                    <a href="#" style="color: var(--text-secondary); text-decoration: none; font-size: 13px; display: flex; align-items: center; gap: 4px;">
                        <i class="ti ti-message-circle"></i> Commenter
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
