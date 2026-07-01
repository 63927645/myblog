<?php
/**
 * Adminer template
 * @since 2.5.7
 *
 * @package FileManagerAdvanced
 */

defined( 'ABSPATH' ) || exit;

?>

<h2 class="dropbox__heading">DB Access <span class="dropbox__heading-pro-tag">PRO</span></h2>

<div class="afm__adminer-wrapper">
    <div class="afm__sidebar">
        <div class="afm__container">

            <div class="afm__header-box">

                <div class="afm_ch1 afm__placeholder afm__placeholder-secondary"></div>
                <div class="afm_ch2 afm__mt-15 afm__placeholder afm__placeholder-primary"></div>

            </div>
            
            <div class="afm__header-secondary-box">
                <div class="afm__ml-20">
                    <div class="afm__mt-15">
                        <div style="width: 120px" class="afm__inline-block afm__placeholder afm__placeholder-primary-gradient"></div>
                        <div style="width: 73px" class="afm__inline-block afm__placeholder afm__placeholder-primary-gradient"></div>
                    </div>
                    <div class="afm__mt-15">
                        <div style="width: 56px" class="afm__inline-block afm__placeholder afm__placeholder-primary-gradient"></div>
                        <div style="width: 120px" class="afm__inline-block afm__placeholder afm__placeholder-primary-gradient"></div>
                    </div>
                </div>
            </div>

            <div class="afm__database">

                <?php $counter = 0; ?>
                <?php $reset_on_3 = 0; ?>
                <?php while ( $counter <= 5 ) : ?>

                    <?php if ( $reset_on_3 < 4 ) : ?>
                        <div class="afm__m-5 afm__placeholder afm__placeholder-primary-gradient"></div>
                    <?php else : ?>

                        <?php $rand_range_2_3 = wp_rand( 2, 3 ); ?>
                        <?php for ( $i = 0; $i < $rand_range_2_3; $i++ ) : ?>
                            <div style="width: <?php echo wp_rand( 172, 206 ); ?>px" class="afm__m-5 afm__placeholder afm__placeholder-primary-gradient"></div>
                        <?php endfor; ?>

                        <?php $reset_on_3 = 0; ?>
                    <?php endif; ?>

                    <?php $reset_on_3++; ?>
                    <?php $counter++; ?>
                <?php endwhile; ?>

            </div>

        </div>
    </div>

    <div class="afm__main-content">

        <div class="fma__container">
            <div class="afm__header mt-22 clearfix">
                <div class="float-left afm__placeholder afm__placeholder-primary"></div>

                <div class="float-right">
                    <button class="fma__logout-btn" disabled>Logout</button>
                </div>
            </div>

            <div class="afm__secondary-header">

                <div class="afm__inline afm__placeholder afm__placeholder-secondary"></div>
                <div class="afm__inline afm__placeholder afm__placeholder-secondary"></div>
                <div class="afm__inline afm__placeholder afm__placeholder-secondary"></div>

            </div>
            
            <div class="afm__secondary-header">
                <div style="margin-left: 26px; width: 204px;background-color: #c2c2c2;" class="afm__placeholder afm__placeholder-secondary"></div>
            </div>

            <div class="afm__search ml-26 afm__secondary-header">
                <div class="afm__search-field"></div>
            </div>

            <div class="ml-26 mt-10">
                
                <table class="afm__table-wrapper">
                    <?php $counter = 0; ?>
                    <?php while ( $counter < 5 ) : ?>

                        <tr class="<?php echo ! $counter ? 'afm__table-header' : 'fma__table--content' ?> afm__table-row">
                            <td style="width: 3%;">
                                <div class="afm__checkbox"></div>
                            </td>
                            <td style="width: 22%;">
                                <?php if ( ! $counter ) : ?>
                                    <div class="ch-1 afm__placeholder afm__placeholder-secondary"></div>
                                <?php else : ?>
                                    <div class="ch-1 afm__placeholder afm__placeholder-primary-gradient"></div>
                                <?php endif; ?>
                            </td>
                            <td style="width: 10%;">
	                            <?php if ( ! $counter ) : ?>
                                    <div class="ch-2 afm__placeholder afm__placeholder-secondary"></div>
	                            <?php else : ?>
                                    <div class="ch-2 afm__placeholder afm__placeholder-secondary-gradient"></div>
	                            <?php endif; ?>
                            </td>
                            <td style="width: 29%;">
	                            <?php if ( ! $counter ) : ?>
                                    <div class="ch-3 afm__placeholder afm__placeholder-secondary"></div>
	                            <?php else : ?>
                                    <div class="ch-3 afm__placeholder afm__placeholder-secondary-gradient"></div>
	                            <?php endif; ?>
                            </td>
                            <td style="width: 7%;">
	                            <?php if ( ! $counter ) : ?>
                                    <div class="ch-4 afm__placeholder afm__placeholder-secondary"></div>
	                            <?php else : ?>
                                    <div class="ch-4 afm__placeholder afm__placeholder-primary-gradient-l"></div>
	                            <?php endif; ?>
                            </td>
                            <td style="width: 12%;">
	                            <?php if ( ! $counter ) : ?>
                                    <div class="ch-7 afm__placeholder afm__placeholder-secondary"></div>
	                            <?php else : ?>
                                    <div class="ch-5 afm__placeholder afm__placeholder-primary-gradient-l"></div>
	                            <?php endif; ?>
                            </td>
                            <td style="width: 7%;">
	                            <?php if ( ! $counter ) : ?>
                                    <div class="ch-8 afm__placeholder afm__placeholder-secondary"></div>
	                            <?php else : ?>
                                    <div class="ch-6 afm__placeholder afm__placeholder-primary-gradient-l"></div>
	                            <?php endif; ?>
                            </td>
                            <td style="width: 10%;">
	                            <?php if ( ! $counter ) : ?>
                                    <div class="ch-9 afm__placeholder afm__placeholder-secondary"></div>
	                            <?php endif; ?>
                            </td>
                        </tr>

                    <?php $counter++; ?>
                    <?php endwhile; ?>
                </table>
                
            </div>

            <div style="width: 634px; height: 62px;" class="afm__search ml-26 afm__secondary-header">
                <div style="margin-left: 12px;margin-top: 20px;">
                    <button class="fma__logout-btn" disabled>Analyze</button>
                    <button class="fma__logout-btn" disabled>Optimize</button>
                    <button class="fma__logout-btn" disabled>Check</button>
                    <button class="fma__logout-btn" disabled>Repair</button>
                    <button class="fma__logout-btn" disabled>Truncate</button>
                    <button class="fma__logout-btn" disabled>Drop</button>
                    <button class="fma__logout-btn" disabled>Copy</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="fma__pro-popup" style="display: none;" id="fma__pro_popup">
    <div class="fma__pro-popup-wrapper">

        <div class="fma__pro-close-button">
            <a id="close-popup-btn" href="#">
                <img src="<?php echo plugin_dir_url( __FILE__ ) ?>../application/assets/images/close-popup.svg" alt="">
            </a>
        </div>

        <div class="fma__pro-popup-content">

            <div>
                <img src="<?php echo FMA_PLUGIN_URL ?>application/assets/images/fma-logo.svg" alt="">
            </div>

            <div class="afmp__pro-popup-desc">
                <p>
                    Get advanced features with Advanced File Manager Pro!
                </p>
            </div>

            <div class="fma__pro-popup-cta">
                <a target="_blank" href="https://advancedfilemanager.com/pricing/?utm_source=plugin&utm_medium=db_access_banner&utm_campaign=plugin">
                    <img style="width: 20px;margin-bottom: -3px;" src="<?php echo FMA_PLUGIN_URL ?>application/assets/images/crown.svg" alt="">
                    Get Pro Now
                    <img style="width: 10px;margin-bottom: -2px;" src="<?php echo FMA_PLUGIN_URL ?>application/assets/images/right-arrow.svg" alt="">
                </a>
            </div>

        </div>

    </div>
</div>

<script>
    jQuery( document ).ready( function() {

        jQuery( '.dropbox__heading, .afm__adminer-wrapper' ).on( 'click', function() {
            jQuery( '#fma__pro_popup' ).show();
        } );

        jQuery( '#close-popup-btn' ).on( 'click', function( e ) {
            e.preventDefault();
            jQuery( '#fma__pro_popup' ).hide();
        } );
    } );
</script>
