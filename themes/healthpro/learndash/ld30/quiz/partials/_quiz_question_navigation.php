<?php
/**
 * ! Extracted from Show Quiz Questions Box
 *
 * Available Variables:
 *
 * @var object $quiz_view      WpProQuiz_View_FrontQuiz instance.
 * @var object $quiz           WpProQuiz_Model_Quiz instance.
 * @var array  $shortcode_atts Array of shortcode attributes to create the Quiz.
 * @var int    $question_count Number of Question to display.
 *
 * @since 3.2.0
 *
 * @package LearnDash\Templates\Legacy\Quiz
 */

defined( 'ABSPATH' ) || exit;
?>

<?php if ( $quiz->getQuizModus() == WpProQuiz_Model_Quiz::QUIZ_MODUS_CHECK && ! $quiz->isSkipQuestionDisabled() && $quiz->isShowReviewQuestion() ) { ?>
	<input type="button" name="skip" value="<?php echo wp_kses_post( // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentBeforeOpen,Squiz.PHP.EmbeddedPhp.ContentAfterOpen
		SFWD_LMS::get_template(
			'learndash_quiz_messages',
			array(
				'quiz_post_id' => $quiz->getID(),
				'context'      => 'quiz_skip_button_label',
				// translators: placeholder: question.
				'message'      => sprintf( esc_html_x( 'Skip %s', 'placeholder: question', 'learndash' ), learndash_get_custom_label_lower( 'question' ) ),
			)
		)
	) ?>" class="et_pb_button et_pb_bg_layout_light wpProQuiz_QuestionButton" style="float: left; margin-right: 10px ;"> <?php // phpcs:ignore Generic.WhiteSpace.ScopeIndent.Incorrect,Squiz.PHP.EmbeddedPhp.ContentBeforeEnd,PEAR.Functions.FunctionCallSignature.Indent,PEAR.Functions.FunctionCallSignature.CloseBracketLine ?>
<?php } ?>
<?php if ( ! is_rtl() ) : ?>
    <input type="button" name="back" value="<?php echo wp_kses_post( // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentBeforeOpen,Squiz.PHP.EmbeddedPhp.ContentAfterOpen
        SFWD_LMS::get_template(
            'learndash_quiz_messages',
            array(
                'quiz_post_id' => $quiz->getID(),
                'context'      => 'quiz_back_button_label',
                'message'      => esc_html__( 'Back', 'learndash' ),
            )
        )
    ) ?>" class="et_pb_button et_pb_bg_layout_light wpProQuiz_QuestionButton" style="float: left ; margin-right: 10px ; display: none;">
<?php else : ?>
	<input type="button" name="next" value="<?php echo wp_kses_post( // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentBeforeOpen,Squiz.PHP.EmbeddedPhp.ContentAfterOpen
		SFWD_LMS::get_template(
			'learndash_quiz_messages',
			array(
				'quiz_post_id' => $quiz->getID(),
				'context'      => 'quiz_next_button_label',
				'message'      => esc_html__( 'Next', 'learndash' ),
			)
		)
	) ?>" class="et_pb_button et_pb_bg_layout_light wpProQuiz_QuestionButton" style="float: left ; margin-right: 10px ; display: none;"> <?php // phpcs:ignore Generic.WhiteSpace.ScopeIndent.Incorrect,Squiz.PHP.EmbeddedPhp.ContentBeforeEnd,PEAR.Functions.FunctionCallSignature.Indent,PEAR.Functions.FunctionCallSignature.CloseBracketLine ?>
<?php endif; ?>
<?php if ( $question->isTipEnabled() ) : ?>
	<input type="button" name="tip" value="<?php echo wp_kses_post( // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentBeforeOpen,Squiz.PHP.EmbeddedPhp.ContentAfterOpen
		SFWD_LMS::get_template(
			'learndash_quiz_messages',
			array(
				'quiz_post_id' => $quiz->getID(),
				'context'      => 'quiz_hint_button_label',
				'message'      => esc_html__( 'Hint', 'learndash' ),
			)
		)
	) ?>" class="et_pb_button et_pb_bg_layout_light wpProQuiz_QuestionButton wpProQuiz_TipButton" style="float: left ; display: inline-block; margin-right: 10px ;"> <?php // phpcs:ignore Generic.WhiteSpace.ScopeIndent.Incorrect,Squiz.PHP.EmbeddedPhp.ContentBeforeEnd,PEAR.Functions.FunctionCallSignature.Indent,PEAR.Functions.FunctionCallSignature.CloseBracketLine ?>
<?php endif; ?>
<input type="button" name="check" value="<?php echo wp_kses_post( // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentBeforeOpen,Squiz.PHP.EmbeddedPhp.ContentAfterOpen
	SFWD_LMS::get_template(
		'learndash_quiz_messages',
		array(
			'quiz_post_id' => $quiz->getID(),
			'context'      => 'quiz_check_button_label',
			'message'      => esc_html__( 'Check', 'learndash' ),
		)
	)
) ?>" class="et_pb_button et_pb_bg_layout_light wpProQuiz_QuestionButton" style="float: right ; margin-right: 10px ; display: none;"> <?php // phpcs:ignore Generic.WhiteSpace.ScopeIndent.Incorrect,Squiz.PHP.EmbeddedPhp.ContentBeforeEnd,PEAR.Functions.FunctionCallSignature.Indent,PEAR.Functions.FunctionCallSignature.CloseBracketLine ?>
<?php if ( ! is_rtl() ) : ?>
    <input type="button" name="next" value="<?php echo wp_kses_post( // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentBeforeOpen,Squiz.PHP.EmbeddedPhp.ContentAfterOpen
        SFWD_LMS::get_template(
            'learndash_quiz_messages',
            array(
                'quiz_post_id' => $quiz->getID(),
                'context'      => 'quiz_next_button_label',
                'message'      => esc_html__( 'Next', 'learndash' ),
            )
        )
    ) ?>" class="et_pb_button et_pb_bg_layout_light wpProQuiz_QuestionButton" style="float: right; display: none;">
<?php else : ?>
    <input type="button" name="back" value="<?php echo wp_kses_post( // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentBeforeOpen,Squiz.PHP.EmbeddedPhp.ContentAfterOpen
        SFWD_LMS::get_template(
            'learndash_quiz_messages',
            array(
                'quiz_post_id' => $quiz->getID(),
                'context'      => 'quiz_back_button_label',
                'message'      => esc_html__( 'Back', 'learndash' ),
            )
        )
    ) ?>" class="et_pb_button et_pb_bg_layout_light wpProQuiz_QuestionButton" style="float: right; display: none;">
<?php endif; ?>