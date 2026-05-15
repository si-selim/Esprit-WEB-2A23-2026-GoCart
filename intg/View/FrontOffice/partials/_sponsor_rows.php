<?php
/**
 * Partial: render sponsor table rows.
 * Expected vars: $rows (array), $showActions, $deleteOnly, $viewOnly, $chooseButton, $extraParams, $formParams
 */
foreach ($rows as $row):
    $pageWeb = $row['pageWeb'] ?? '';
    $href    = (strpos($pageWeb, 'http') === 0 ? $pageWeb : 'https://' . $pageWeb);
?>
    <tr>
    <?php if ($chooseButton): ?>
        <td><?php echo htmlspecialchars($row['idSponsor']); ?></td>
        <td><?php echo htmlspecialchars($row['nom']); ?></td>
        <td><?php echo htmlspecialchars($row['type']); ?></td>
        <td><?php echo htmlspecialchars($row['email']); ?></td>
        <td><?php echo htmlspecialchars($row['contact']); ?></td>
        <td>
            <a href="addSponsoring.php?idSponsor=<?php echo $row['idSponsor'] . $extraParams . $formParams; ?>" class="btn btn-primary">Choisir</a>
        </td>
    <?php else: ?>
        <?php if ($deleteOnly): ?>
            <td><?php echo htmlspecialchars($row['idUser'] ?? ''); ?></td>
        <?php endif; ?>
        <td><?php echo htmlspecialchars($row['idSponsor']); ?></td>
        <td><?php echo htmlspecialchars($row['nom']); ?></td>
        <td><?php echo htmlspecialchars($row['type']); ?></td>
        <td><?php echo htmlspecialchars($row['adresse']); ?></td>
        <td><?php echo htmlspecialchars($row['contact']); ?></td>
        <td><?php echo htmlspecialchars($row['email']); ?></td>
        <td>
            <a href="<?php echo htmlspecialchars($href); ?>" target="_blank" rel="noopener noreferrer">
                <?php echo htmlspecialchars($row['nom']); ?>
            </a>
        </td>
        <?php if ($showActions || $deleteOnly || $viewOnly): ?>
            <td>
                <?php if ($showActions): ?>
                    <button class="btn btn-secondary view-sponsoring-btn" data-sponsor-id="<?php echo $row['idSponsor']; ?>">Voir sponsoring</button>
                    <div class="row-actions">
                        <a href="deleteSponsor.php?id=<?php echo $row['idSponsor']; ?>"
                           class="icon-btn icon-delete delete-sponsor-btn"
                           data-sponsor-name="<?php echo htmlspecialchars($row['nom'], ENT_QUOTES); ?>"
                           title="Supprimer"></a>
                        <a href="modifySponsor.php?id=<?php echo $row['idSponsor']; ?>" class="icon-btn icon-edit" title="Modifier"></a>
                    </div>
                <?php elseif ($deleteOnly): ?>
                    <div class="row-actions">
                        <a href="deleteSponsor.php?id=<?php echo $row['idSponsor']; ?>"
                           class="icon-btn icon-delete delete-sponsor-btn"
                           data-sponsor-name="<?php echo htmlspecialchars($row['nom'], ENT_QUOTES); ?>"
                           title="Supprimer"></a>
                    </div>
                <?php elseif ($viewOnly): ?>
                    <button class="btn btn-secondary view-sponsoring-btn" data-sponsor-id="<?php echo $row['idSponsor']; ?>">Voir sponsoring</button>
                <?php endif; ?>
            </td>
        <?php endif; ?>
    <?php endif; ?>
    </tr>
<?php endforeach; ?>
