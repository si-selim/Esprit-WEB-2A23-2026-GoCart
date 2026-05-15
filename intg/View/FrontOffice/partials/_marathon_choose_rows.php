<?php
/**
 * Partial: render marathon rows with optional "Choisir" button.
 * Expected vars: $rows, $chooseButton, $extraParams, $formParams
 */
foreach ($rows as $row): ?>
    <tr>
        <td><?php echo htmlspecialchars($row['id_marathon']); ?></td>
        <td><?php echo htmlspecialchars($row['nom_marathon']); ?></td>
        <td><?php echo htmlspecialchars($row['region_marathon']); ?></td>
        <td><?php echo htmlspecialchars($row['date_marathon']); ?></td>
        <?php if ($chooseButton): ?>
            <td>
                <a href="addSponsoring.php?idMarathon=<?php echo $row['id_marathon'] . $extraParams . $formParams; ?>" class="btn btn-primary">Choisir</a>
            </td>
        <?php endif; ?>
    </tr>
<?php endforeach; ?>
