DELIMITER //
CREATE PROCEDURE NormalizeCoverUrl()
BEGIN
    -- Delete duplicate rows based on normalized image_url
    delete from dominant_colors where dominant_colors.image_url IN (SELECT
        REPLACE(image_url, 'https://cybeat.sibeux.my.id/cloud-music-player/api/music/stream?file_type=image&cover_url=', '') AS normalized
    FROM dominant_colors
    GROUP BY normalized
    HAVING  COUNT(*) > 1);

    -- Update image_url to remove the prefix
    UPDATE dominant_colors
    SET image_url = REPLACE(image_url, 'https://cybeat.sibeux.my.id/cloud-music-player/api/music/stream?file_type=image&cover_url=', '');
END //
DELIMITER ;