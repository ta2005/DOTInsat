<div style="
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 60vh;
    text-align: center;
    color: #EDEDED;
    gap: 16px;
">
    <h1 style="font-size: 6rem; font-weight: 700; margin: 0; opacity: 0.15;">
        <?= $code ?>
    </h1>
    <h2 style="font-size: 1.8rem; font-weight: 600; margin: 0;">
        <?= htmlspecialchars($title) ?>
    </h2>
    <p style="font-size: 1.1rem; color: #B0B0B0; max-width: 400px;">
        <?= htmlspecialchars($message) ?>
    </p>
    <a href="/?page=home" style="
        display: inline-block;
        margin-top: 10px;
        padding: 12px 28px;
        background: #EDEDED;
        color: #161616;
        border-radius: 25px;
        text-decoration: none;
        font-weight: 500;
        font-size: 1rem;
    ">
        Retour à l'accueil
    </a>
</div>