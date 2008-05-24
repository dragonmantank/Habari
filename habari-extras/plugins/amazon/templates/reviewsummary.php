<div class="amazon-item">
  <div class="amazon-image" style="width: 160px; float: left;">
    <a href="<?php echo (string)$item->DetailPageURL; ?>"><img src="<?php echo (string)$item->MediumImage->URL; ?>" style="width: <?php echo (int)$xml->Items->Item->MediumImage->Width; ?>; height: <?php echo (int)$item->MediumImage->Height; ?>px; border: 0px;" alt="<?php echo (string)$item->ItemAttributes->Title; ?>" /></a>
  </div>
  <div class="amazon-detail" style="float: left; margin-left: 8px;">
    <div class="amazon-title"><a href="<?php echo (string)$xml->Items->Item->DetailPageURL; ?>"><?php echo (string)$item->ItemAttributes->Title; ?></a></div>
<?php
            if ( isset( $item->ItemAttributes->Creator[0] ) ) echo (string)$item->ItemAttributes->Creator[0] . '<br />'; 
            if ( isset( $item->ItemAttributes->Publisher ) ) echo (string)$item->ItemAttributes->Publisher . '<br />';
            if ( isset( $item->SalesRank ) ) echo _t('Sales Rank: ') . (int)$item->SalesRank . '<br />';
            if ( isset( $item->CustomerReviews->AverageRating ) ) {
                echo '<br />';
                echo _t('Average Rating: ') . $this->ratingToStarImage( (float)$item->CustomerReviews->AverageRating ) . '<br />';
                for ( $i = 0; $i < 5; $i++ ) {
                    if ( !isset( $item->CustomerReviews->Review[$i]) ) break;
                    echo $this->ratingToStarImage( (int)$item->CustomerReviews->Review[$i]->Rating ) . ' ' . (string)$item->CustomerReviews->Review[$i]->Summary . '<br />';
                }
            }
?>
    
  </div>
  <div class="amazon-clear" style="clear: both;"></div>
</div>