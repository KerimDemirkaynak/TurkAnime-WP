<?php get_header(); ?>
<?php while (have_posts()) : the_post(); ?>
<article <?php post_class('panel page-article'); ?>>
    <h1 class="page-title"><?php the_title(); ?></h1>
    <div class="entry"><?php the_content(); ?></div>
</article>
<?php if (comments_open() || get_comments_number()) comments_template(); ?>
<?php endwhile; ?>
<?php get_footer(); ?>
