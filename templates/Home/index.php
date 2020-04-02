<div class="action-container">
    <h2>Exact</h2>
    <div class="row">
        <div class="info">
            <div><em>Gebruiker:</em></div>
            <div><?= $accountName ? $accountName : "Niet gekoppeld" ?></div>
        </div>
        <a class="button" href="/home/<?= $accountName ? "removeExact" : "connectExact" ?>"><?= $accountName ? "verwijderen" : "koppelen" ?></a>
    </div>
</div>
<div class="action-container">
    <h2>Turfwaar</h2>
    <div class="row">
        <div class="info">
            <div><em>Laatste update:</em></div>
            <div><?= $daysSinceUpdate ?></div>
        </div>
        <a class="button" <?= $accountName ?  "href='/home/updateProducts'" : "disabled" ?>>updaten</a>
    </div>
</div>
<div class="action-container">
    <h2>Turfhistorie</h2>
    <div class="row">
        <div>
            <div class="info">
                <div><em>Laatste export:</em></div>
                <div><?= $daysSinceExport ?></div>
            </div>
            <div class="info">
                <div><em>Nieuwe turfjes:</em></div>
                <div><?= $turfjes ?></div>
            </div>
        </div>

        <a class="button" <?= $accountName ?  "href='/home/exportHistory'" : "disabled" ?>>exporteren</a>
    </div>
</div>