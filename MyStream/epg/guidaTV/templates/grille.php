<table id="grille" border="1">
    <thead>
        <tr>
            <th id="previous">&lt;</th>
            <th><?php echo $this->startHour; ?></th>
            <th><?php echo $this->endHour - $this->startHour ?></th>
            <th><?php echo $this->endHour; ?></th>
            <th id="next">&gt;</th>
        </tr>
    </thead>
    <tbody>
    <?php $lengthMax = 3 * 60 * 60; ?>
    <?php foreach ($this->channels as $channel) : ?>
        <tr>
            <!-- img title -->
            <th>
                <img src="<?php echo $channel->icon ?>" title="<?php echo $channel->name ?>"
                    alt="<?php echo $channel->name ?>" />
            </th>
            <td colspan="4">
                <table border="1">
                    <tr>
                    <?php $lengthTotal = 0; ?>
                    <?php foreach ($channel->programs as $program) : ?>
                        <?php
                            $length = $program->length->number;
                            if ($program->start < $this->beginDate) {
                                // Le debut est tronqué
                                $length -= $this->beginDate - $program->start;
                            } elseif ($program->stop > $this->endDate) {
                                // La fin est tronquée
                                $length -= $program->stop - $this->endDate;
                            }
                            if (($lengthTotal+$length) > $lengthMax) {
                                $length = $lengthMax - $lengthTotal;
                            }
                            $lengthTotal += $length;
                            $width = ($length * 800) / $lengthMax;
                        ?>
                        <td style="<?php echo $width ?>px">
                            <?php echo $program->title . '/' . $program->subTitle; ?>
                        </td>
                    <?php endforeach; ?>
                    <?php
                        if ($lengthTotal < $lengthMax) {
                            echo '<td class="hatched" style="width:'.($lengthMax-$lengthTotal).'px">&nbsp;</td>';
                        }
                    ?>
                    </tr>
                </table>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
