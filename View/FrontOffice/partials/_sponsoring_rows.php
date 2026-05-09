<?php
/**
 * Partial: render sponsoring table rows.
 * Expected vars: $rows (array), $showActions (bool), $deleteOnly (bool), $viewOnly (bool)
 */
foreach ($rows as $row): ?>
    <tr>
        <td><?php echo htmlspecialchars($row['idSponsoring']); ?></td>
        <td><?php echo htmlspecialchars($row['nomSponsoring']); ?></td>
        <td><?php echo htmlspecialchars($row['dateDebut']); ?></td>
        <td><?php echo htmlspecialchars($row['dateFin']); ?></td>
        <td><?php echo htmlspecialchars($row['montant']); ?> €</td>
        <td><?php echo htmlspecialchars($row['etat']); ?></td>
        <td><?php echo htmlspecialchars($row['idSponsor']); ?></td>
        <td><?php echo htmlspecialchars($row['idMarathon']); ?></td>
        <?php if ($showActions || $deleteOnly || $viewOnly): ?>
            <td>
                <?php if ($showActions): ?>
                    <div class="row-actions">
                        <a href="deleteSponsoring.php?id=<?php echo $row['idSponsoring']; ?>"
                           class="icon-btn icon-delete delete-sponsoring-btn"
                           data-sponsoring-name="<?php echo htmlspecialchars($row['nomSponsoring'], ENT_QUOTES); ?>"
                           title="Supprimer"></a>
                        <a href="modifySponsoring.php?id=<?php echo $row['idSponsoring']; ?>" class="icon-btn icon-edit" title="Modifier"></a>
                    </div>
                <?php elseif ($deleteOnly): ?>
                    <div class="row-actions">
                        <a href="deleteSponsoring.php?id=<?php echo $row['idSponsoring']; ?>"
                           class="icon-btn icon-delete delete-sponsoring-btn"
                           data-sponsoring-name="<?php echo htmlspecialchars($row['nomSponsoring'], ENT_QUOTES); ?>"
                           title="Supprimer"></a>
                    </div>
                <?php elseif ($viewOnly): ?>
                    <a href="#sponsoring" class="btn btn-secondary">Voir sponsoring</a>
                    <a href="#fournitures" class="btn btn-secondary">Voir fourniture</a>
                <?php endif; ?>
            </td>
        <?php endif; ?>
    </tr>
<?php endforeach; ?>
