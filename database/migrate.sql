-- ============================================================
-- Guest Emotional Journey Mapping — Database Migration
-- Run this against existing restaurant_db to add new features
-- ============================================================

USE restaurant_db;

-- -------------------------------------------------------
-- 1. RESERVASI TABLE — Add emotional preference columns
-- -------------------------------------------------------
ALTER TABLE reservasi
    ADD COLUMN mood_preference VARCHAR(50) DEFAULT NULL AFTER preferensi_menu,
    ADD COLUMN favorite_flavor VARCHAR(50) DEFAULT NULL AFTER mood_preference,
    ADD COLUMN special_occasion VARCHAR(50) DEFAULT 'None' AFTER favorite_flavor;

-- -------------------------------------------------------
-- 2. MENU TABLE — Add emotional journey content columns
-- -------------------------------------------------------
ALTER TABLE menu
    ADD COLUMN story_behind TEXT DEFAULT NULL AFTER rating_menu,
    ADD COLUMN recommended_atmosphere VARCHAR(255) DEFAULT NULL AFTER story_behind,
    ADD COLUMN mood_fit TEXT DEFAULT NULL AFTER recommended_atmosphere,
    ADD COLUMN aroma_notes VARCHAR(255) DEFAULT NULL AFTER mood_fit,
    ADD COLUMN texture_profile VARCHAR(255) DEFAULT NULL AFTER aroma_notes,
    ADD COLUMN presentation_style VARCHAR(255) DEFAULT NULL AFTER texture_profile;

-- -------------------------------------------------------
-- 3. FEEDBACK TABLE — Add multi-sensory rating columns
-- -------------------------------------------------------
ALTER TABLE feedback
    ADD COLUMN rating_aroma FLOAT DEFAULT NULL AFTER rating,
    ADD COLUMN rating_texture FLOAT DEFAULT NULL AFTER rating_aroma,
    ADD COLUMN rating_presentation FLOAT DEFAULT NULL AFTER rating_texture,
    ADD COLUMN rating_story FLOAT DEFAULT NULL AFTER rating_presentation,
    ADD COLUMN overall_emotion VARCHAR(50) DEFAULT NULL AFTER rating_story;

-- -------------------------------------------------------
-- 4. MEMORY JOURNEY TABLE — New table for post-meal memories
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS memory_journey (
    id_memory VARCHAR(50) PRIMARY KEY,
    id_pelanggan VARCHAR(50),
    id_menu VARCHAR(50),
    id_reservasi VARCHAR(50),
    emotional_memory TEXT,
    reinforcement_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pelanggan) REFERENCES pelanggan(id_pelanggan) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_menu) REFERENCES menu(id_menu) ON DELETE CASCADE ON UPDATE CASCADE
);

-- -------------------------------------------------------
-- 5. SEED DATA — Populate emotional journey content for existing menus
-- -------------------------------------------------------

UPDATE menu SET
    story_behind = 'Born in the rolling hills of Piedmont, Italy, this risotto pays homage to generations of truffle hunters who brave dawn mists to unearth the elusive black truffle. Each grain of premium Arborio rice is patiently stirred, absorbing rich porcini broth until it reaches a velvety, soul-warming consistency — a dish that whispers elegance with every bite.',
    recommended_atmosphere = 'Candlelit intimacy with soft jazz undertones — perfect for a romantic evening or a moment of refined solitude.',
    mood_fit = 'This dish resonates deeply with those seeking comfort and sophistication. Its earthy warmth wraps around you like a cashmere blanket, making it ideal for romantic dinners or reflective celebrations.',
    aroma_notes = 'Earthy black truffle, toasted garlic, aged Parmigiano-Reggiano, hints of wild thyme',
    texture_profile = 'Creamy and velvety with al dente rice grains, enriched with a silky butter finish',
    presentation_style = 'Served in a warm artisan ceramic bowl, garnished with shaved truffle, micro herbs, and a drizzle of truffle-infused olive oil'
WHERE id_menu = 'MNU-001';

UPDATE menu SET
    story_behind = 'A timeless classic from the heart of Burgundy, France, this dish transforms humble garden snails into an extraordinary culinary experience. Each escargot is nestled in its shell, bathed in a fragrant compound butter infused with fresh garlic, flat-leaf parsley, and a whisper of Pernod — a celebration of French terroir on every plate.',
    recommended_atmosphere = 'Warm bistro ambiance with golden lighting and the soft clink of wine glasses — an invitation to slow down and savor.',
    mood_fit = 'Perfect for the adventurous soul who craves authenticity and cultural immersion. This dish sparks conversation and creates shared moments of culinary discovery.',
    aroma_notes = 'Roasted garlic butter, fresh parsley, subtle anise from Pernod, warm bread crust',
    texture_profile = 'Tender and slightly chewy escargot enveloped in rich, herbaceous melted butter',
    presentation_style = 'Presented on a traditional escargot plate with specialized tongs, accompanied by crusty baguette slices and a lemon wedge'
WHERE id_menu = 'MNU-002';

UPDATE menu SET
    story_behind = 'The quintessential French dessert, our Crème Brûlée is crafted from a recipe passed down through three generations of Parisian pâtissiers. Madagascar vanilla beans are steeped in velvety custard for 24 hours, then crowned with a paper-thin caramelized sugar crust that shatters at the tap of a spoon — pure culinary poetry.',
    recommended_atmosphere = 'Elegant dessert lounge setting with warm amber lighting and the faint melody of a piano — the perfect finale to an unforgettable evening.',
    mood_fit = 'A dessert that transforms any occasion into a celebration. Its delicate sweetness and dramatic presentation evoke joy and a sense of luxurious indulgence.',
    aroma_notes = 'Bourbon vanilla, caramelized sugar, warm custard, subtle citrus zest',
    texture_profile = 'Crisp, glass-like caramel shell giving way to silky, cloud-soft vanilla custard',
    presentation_style = 'Served in a classic porcelain ramekin atop a gold-rimmed dessert plate, with a side of fresh berries and an edible flower garnish'
WHERE id_menu = 'MNU-003';
