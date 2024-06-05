<?php

namespace Faker\Provider;
class Commerce extends Base
{

    protected static $promoCoupon = [
        'adjective' => ['Amazing', 'Awesome', 'Cool', 'Fantastic', 'Incredible', 'Fabulous', 'Superb', 'Marvelous', 'Terrific', 'Wonderful',
            'Spectacular', 'Exceptional', 'Phenomenal', 'Outstanding', 'Remarkable', 'Impressive', 'Excellent', 'Splendid', 'Stunning',
            'Brilliant', 'Unbelievable', 'Extraordinary', 'Astonishing', 'Dazzling', 'Breathtaking', 'Magnificent', 'Glorious', 'Sublime', 'Radiant', 'Majestic'
        ],
        'noun' => ['Code', 'Deal', 'Discount', 'Offer', 'Sale', 'Promotion', 'Special', 'Bargain', 'Savings', 'Coupon', 'Voucher', 'Price',
            'Gift', 'Reward', 'Treat', 'Benefit', 'Perk', 'Bonus', 'Win', 'Prize', 'Incentive', 'Package', 'Gift card', 'Present', 'Surprise', 'Token',
            'Goodie', 'Value', 'Delight', 'Perquisite'
        ]

    ];
    protected static $department = [
        "Sales", "Marketing", "Human Resources", "Finance", "Operations", "Customer Service",
        "Research and Development", "Information Technology", "Product Management", "Supply Chain Management",
        "Quality Assurance", "Legal", "Public Relations", "Business Development", "Accounting",
        "Administration", "Purchasing", "Training and Development", "Corporate Communications",
        "Strategic Planning", "Facilities Management", "Risk Management", "Internal Audit", "Project Management",
        "Creative Services", "E-commerce", "Vendor Management", "Logistics", "Regulatory Compliance",
        "Sustainability", "Market Research", "Brand Management", "Procurement", "Community Relations",
        "Employee Relations", "Data Analytics", "Event Planning", "Health and Safety", "Investor Relations",
        "Merchandising", "Social Media", "Talent Acquisition", "Training and Development",
        "Warehouse Management", "Strategic Partnerships", "Corporate Development"
    ];

    protected static $category = [
        "Electronics", "Apparel", "Home & Kitchen", "Beauty & Personal Care", "Books", "Toys & Games",
        "Health & Wellness", "Sports & Outdoors", "Automotive", "Grocery & Gourmet Food", "Pet Supplies",
        "Office Products", "Tools & Home Improvement", "Baby Products", "Jewelry", "Electrical & Lighting",
        "Furniture", "Arts & Crafts", "Industrial & Scientific", "Musical Instruments", "Software",
        "Mobile Accessories", "Outdoor Living", "Luggage & Travel Gear", "Camera & Photo", "Party Supplies",
        "Food & Beverage", "Garden & Outdoor", "Electrical Equipment", "Medical Supplies", "Home Improvement",
        "Computer Accessories", "School & Office Supplies", "Building Materials", "Kitchen & Dining",
        "Electrical Components", "Automotive Parts", "Cleaning Supplies", "Craft Supplies", "Patio & Garden",
        "Personal Care", "Travel Accessories", "Home Decor", "Storage & Organization", "Home Appliances",
        "Electrical Appliances", "Construction Materials", "Building Supplies", "Art Supplies"
    ];

    protected static $productName = [
        'adjective' => ['Small', 'Rustic', 'Ergonomic', 'Modern', 'Vintage', 'Sleek', 'Compact', 'Stylish', 'Luxurious',
            'Efficient', 'Durable', 'Versatile', 'Innovative', 'Premium', 'Customizable', 'Handcrafted',
            'Chic', 'Minimalist', 'Practical', 'Elegant', 'Contemporary', 'Sophisticated', 'Robust', 'Trendy',
            'Futuristic', 'Affordable', 'Unique', 'Functional', 'Fashionable', 'Timeless', 'High-quality', 'Exclusive',
            'Glamorous', 'Eclectic', 'Dynamic', 'Creative', 'Adaptable', 'Refined', 'Sustainable', 'Artisan',
            'Compact', 'Vibrant', 'Cosy', 'Gorgeous', 'Whimsical', 'Charming', 'Fresh', 'Cozy', 'Soothing'
        ],
        'material' => ['Wood', 'Paper', 'Metal', 'Glass', 'Leather', 'Plastic', 'Fabric', 'Ceramic', 'Stone', 'Bamboo',
            'Acrylic', 'Canvas', 'Rubber', 'Stainless Steel', 'Aluminum', 'Carbon Fiber', 'Wicker', 'Velvet', 'Suede',
            'Granite', 'Marble', 'Brass', 'Bronze', 'Copper', 'Porcelain', 'Silicone', 'Linen', 'Velour', 'Mahogany',
            'Pine', 'Oak', 'Maple', 'Teak', 'Birch', 'Cherry Wood', 'Walnut', 'Cotton', 'Silk', 'Wool', 'Polyester',
            'Fur', 'Fiberglass', 'Vinyl', 'Laminate', 'MDF (Medium-Density Fiberboard)', 'HDPE (High-Density Polyethylene)', 'PVC (Polyvinyl Chloride)'
        ],
        'product' => ['Chair', 'Car', 'Pants', 'Plate', 'Table', 'Phone', 'Watch', 'Shoes', 'Bag', 'Lamp', 'Camera', 'Book',
            'Speaker', 'Knife', 'Sofa', 'Hat', 'Bike', 'Glasses', 'Headphones', 'Guitar', 'Ring', 'Wallet', 'Scarf',
            'Monitor', 'Printer', 'Painting', 'Earrings', 'Backpack', 'Desk', 'Keyboard', 'Vase', 'Mug', 'Mirror',
            'Jacket', 'Sunglasses', 'Drone', 'Fan', 'Rug', 'Clock', 'Sculpture', 'Blender', 'Chair', 'Bench', 'Couch', 'Chandelier',
            'Necklace', 'Oven', 'Watch', 'Pot', 'Easel', 'Bottle', 'Calendar'
        ]

    ];

    protected static $productDescription = [
        "Stylish Wooden Chair with Sleek Design for Modern Living Spaces",
        "Luxurious Leather Sofa for Contemporary Home Decor and Comfortable Lounging",
        "Premium Glass Table featuring Elegant Finish for Sophisticated Dining Experiences",
        "Innovative Aluminum Laptop with High-quality Display for Efficient Work Performance",
        "Chic Fabric Backpack with Trendy Design for Fashionable Travelers on the Go",
        "Compact Stainless Steel Watch with Minimalist Dial for Timeless Style Statements",
        "Elegant Metal Lamp with Contemporary Touch for Stylish Room Illumination",
        "Creative Canvas Painting showcasing Whimsical Artistry for Inspiring Wall Decor",
        "Dynamic Carbon Fiber Bike for Adventurous Rides through Urban Landscapes",
        "Versatile Plastic Chair offering Comfortable Seating Options for Various Settings",
        "Functional Wood Desk providing Practical Workstation for Productive Office Hours",
        "Exclusive Ceramic Vase featuring Refined Craftsmanship for Elegant Home Accents",
        "Modern Acrylic Mirror adding Contemporary Flair to Bedroom or Bathroom Decor",
        "Sustainable Bamboo Speaker delivering High-quality Sound for Music Enthusiasts",
        "Efficient Polyester Backpack with Multiple Compartments for Organized Storage",
        "Handcrafted Velvet Scarf offering Luxurious Feel and Warmth for Cozy Winter Days",
        "Durable Silicone Oven Mitts ensuring Safe Handling during Cooking and Baking",
        "Timeless Leather Wallet with Classic Design for Stylish Storage of Essentials",
        "Charming Cotton Pillowcases with Vibrant Patterns for Refreshing Bedroom Decor",
        "Practical Plastic Desk Organizer providing Neat Storage Solution for Office Supplies",
        "Glamorous Glass Chandelier adding Sparkle and Elegance to Dining Room Ambiance",
        "Unique Bronze Sculpture showcasing Artistic Expression for Home Art Collections",
        "Affordable Stainless Steel Mug perfect for Enjoying Hot Beverages on the Go",
        "Chic Leather Jacket with Trendy Details for Fashion-forward Wardrobe Staples",
        "Cosy Wool Blanket offering Softness and Warmth for Relaxing Movie Nights",
        "Gorgeous Granite Countertop adding Luxury and Durability to Kitchen Spaces",
        "High-quality Ceramic Plate with Stylish Design for Elegant Dining Experiences",
        "Sleek Aluminum Watch featuring Dynamic Dial for Sporty and Stylish Looks",
        "Exclusive Pine Desk with Classic Finish for Timeless Home Office Settings",
        "Refined Marble Vase adding Sophistication and Grace to Living Room Decor",
        "Ergonomic Mesh Office Chair providing Comfortable Seating for Long Work Hours",
        "Innovative Glass Blender with Powerful Motor for Effortless Food Preparation",
        "Fashionable Leather Backpack offering Stylish Storage Solution for Daily Essentials",
        "Dynamic Aluminum Laptop Stand providing Ergonomic Support for Improved Posture",
        "Trendy Canvas Painting with Vibrant Colors for Eye-catching Wall Decor",
        "Compact Plastic Bottle perfect for Hydration on Outdoor Adventures and Workouts",
        "Chic Brass Chandelier adding Elegant Touch to Dining or Living Room Settings",
        "Stylish Ceramic Mug featuring Whimsical Designs for Enjoyable Coffee Moments",
        "Luxurious Velvet Sofa with Plush Upholstery for Comfortable Lounging Spaces",
        "Premium Wood Desk Organizer providing Elegant Storage Solution for Office Supplies",
        "Exclusive Glass Vase with Intricate Patterns for Stunning Floral Arrangements",
        "Charming Linen Pillowcases with Delicate Embroidery for Elegant Bedroom Decor",
        "Efficient Stainless Steel Water Bottle perfect for Hydration on the Go",
        "Versatile Canvas Backpack offering Stylish and Practical Storage for Daily Needs",
        "Functional Plastic Storage Bins providing Neat Organization for Home and Office",
        "Trendy Leather Watch featuring Minimalist Design for Everyday Style Statements",
        "Artisan Wood Sculpture showcasing Intricate Craftsmanship for Home Decor",
        "Innovative Ceramic Mug with Built-in Coaster for Convenient Coffee Enjoyment",
        "Dynamic Metal Desk Lamp providing Adjustable Lighting for Various Tasks",
        "Sustainable Bamboo Cutting Board offering Durable Surface for Food Preparation",
        "Chic Fabric Sofa with Sleek Lines for Modern and Comfortable Living Spaces",
        "Gorgeous Marble Clock adding Timeless Elegance to Home or Office Decor",
        "Luxurious Velvet Pillowcases with Silky Texture for Ultimate Comfort and Style",
        "Exclusive Glass Coffee Table featuring Sophisticated Design for Elegant Living Rooms",
        "Efficient Aluminum Laptop Stand providing Ergonomic Support for Work or Study",
        "Fashionable Leather Jacket with Classic Design for Stylish Wardrobe Essentials",
        "Stylish Canvas Backpack with Multiple Pockets for Organized Storage on the Go",
        "Trendy Plastic Sunglasses offering UV Protection for Fashionable Eye Wear",
        "Dynamic Metal Desk Organizer providing Functional Storage for Office Supplies",
        "Charming Linen Pillowcases with Elegant Embroidery for Cozy Bedroom Decor",
        "Artisan Wood Sculpture showcasing Unique Design for Distinctive Home Accents",
        "Innovative Ceramic Mug with Creative Handle Design for Enjoyable Coffee Moments",
        "Exclusive Glass Vase featuring Intricate Details for Stunning Floral Arrangements",
        "Efficient Stainless Steel Water Bottle perfect for Hydration during Outdoor Activities",
        "Versatile Canvas Backpack offering Stylish and Functional Storage Solution",
        "Functional Plastic Storage Bins providing Neat Organization for Home and Office Use",
        "Trendy Leather Watch featuring Minimalist Design for Everyday Fashion Statements",
        "Artisan Wood Sculpture showcasing Unique Design for Distinctive Home Accents",
        "Innovative Ceramic Mug with Creative Handle Design for Enjoyable Coffee Moments",
        "Exclusive Glass Vase featuring Intricate Details for Stunning Floral Arrangements",
        "Efficient Stainless Steel Water Bottle perfect for Hydration during Outdoor Activities",
        "Versatile Canvas Backpack offering Stylish and Functional Storage Solution",
        "Functional Plastic Storage Bins providing Neat Organization for Home and Office Use",
        "Trendy Leather Watch featuring Minimalist Design for Everyday Fashion Statements",
        "Artisan Wood Sculpture showcasing Unique Design for Distinctive Home Accents",
        "Innovative Ceramic Mug with Creative Handle Design for Enjoyable Coffee Moments",
        "Exclusive Glass Vase featuring Intricate Details for Stunning Floral Arrangements",
        "Efficient Stainless Steel Water Bottle perfect for Hydration during Outdoor Activities",
        "Versatile Canvas Backpack offering Stylish and Functional Storage Solution",
        "Functional Plastic Storage Bins providing Neat Organization for Home and Office Use",
        "Trendy Leather Watch featuring Minimalist Design for Everyday Fashion Statements",
    ];

    protected static $sku; //represents an SKU

    protected static $upc;  //represents a universal product code

    protected static $ean;  // Represents a European Article Number

    public function sku()
    {
        return self::regexify('[A-Z0-9]{8}');
    }

    public function upc()
    {
        return self::regexify('[0-9]{12}');
    }

    public function ean()
    {
        return self::regexify('[0-9]{13}');
    }

    public function category(): string
    {
        return static::randomElement(static::$category);
    }

    public function department(): string
    {
        return static::randomElement(static::$department);
    }

    public function productName(): string
    {
        return static::randomElement(static::$productName['adjective'])
            . ' ' . static::randomElement(static::$productName['material'])
            . ' ' . static::randomElement(static::$productName['product']);
    }

    public function productDescription(): string
    {
        return static::randomElement(static::$productDescription);
    }

    public function promoCoupon(): string
    {
        return static::randomElement(static::$promoCoupon['adjective'])
            . static::randomElement(static::$promoCoupon['noun']);

    }

    public function promoCouponWithDigits(int $digits = 4): string
    {
        return static::randomElement(static::$promoCoupon['adjective'])
            . static::randomElement(static::$promoCoupon['noun'])
            . $this->generator->randomNumber($digits, true);
    }

    public function promoCouponWithDiscount(int $discount): string
    {
        return static::randomElement(static::$promoCoupon['adjective'])
            . static::randomElement(static::$promoCoupon['noun'])
            . $discount;
    }
}

