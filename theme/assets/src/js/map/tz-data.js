// Where we work — IANA time-zone → place lookup.
//
// No network call, no permission prompt: the browser's own resolved time zone
// (Intl.DateTimeFormat().resolvedOptions().timeZone) is mapped to a country,
// city and coordinates from this bundled table.
//
// Entry: zone -> [countryName, city, lat, lon, approx]
//   countryName  must match a name in geo-50m.js (Natural Earth naming).
//   city, lat, lon  the pin position and label.
//   approx  1 when the point is a capital/centroid stand-in for a country-level
//           or city-less zone (shown as "Approximate"); omitted/0 when the zone
//           names the city itself (shown exactly).
//
// Covers the zones real visitors resolve to across the regions we serve. A zone
// that is not here (e.g. Etc/UTC, Factory) is treated as unmapped: the map shows
// the world and invites the opt-in "use my exact location" link.

export const TZ = {
  // ---- Asia / South Asia ----
  "Asia/Colombo": ["Sri Lanka", "Colombo", 6.9271, 79.8612],
  "Asia/Kolkata": ["India", "Mumbai", 19.076, 72.8777],
  "Asia/Calcutta": ["India", "Kolkata", 22.5726, 88.3639],
  "Asia/Karachi": ["Pakistan", "Karachi", 24.8607, 67.0011],
  "Asia/Dhaka": ["Bangladesh", "Dhaka", 23.8103, 90.4125],
  "Asia/Kathmandu": ["Nepal", "Kathmandu", 27.7172, 85.324],
  "Asia/Thimphu": ["Bhutan", "Thimphu", 27.4712, 89.6339],
  "Asia/Kabul": ["Afghanistan", "Kabul", 34.5553, 69.2075],
  // ---- Asia / East & South-East ----
  "Asia/Tokyo": ["Japan", "Tokyo", 35.6762, 139.6503],
  "Asia/Seoul": ["South Korea", "Seoul", 37.5665, 126.978],
  "Asia/Shanghai": ["China", "Shanghai", 31.2304, 121.4737],
  "Asia/Hong_Kong": ["Hong Kong", "Hong Kong", 22.3193, 114.1694],
  "Asia/Taipei": ["Taiwan", "Taipei", 25.033, 121.5654],
  "Asia/Singapore": ["Singapore", "Singapore", 1.3521, 103.8198],
  "Asia/Kuala_Lumpur": ["Malaysia", "Kuala Lumpur", 3.139, 101.6869],
  "Asia/Jakarta": ["Indonesia", "Jakarta", -6.2088, 106.8456],
  "Asia/Bangkok": ["Thailand", "Bangkok", 13.7563, 100.5018],
  "Asia/Ho_Chi_Minh": ["Vietnam", "Ho Chi Minh City", 10.8231, 106.6297],
  "Asia/Manila": ["Philippines", "Manila", 14.5995, 120.9842],
  "Asia/Yangon": ["Myanmar", "Yangon", 16.8409, 96.1735],
  "Asia/Phnom_Penh": ["Cambodia", "Phnom Penh", 11.5564, 104.9282],
  // ---- Middle East ----
  "Asia/Dubai": ["United Arab Emirates", "Dubai", 25.2048, 55.2708],
  "Asia/Qatar": ["Qatar", "Doha", 25.2854, 51.531],
  "Asia/Riyadh": ["Saudi Arabia", "Riyadh", 24.7136, 46.6753],
  "Asia/Kuwait": ["Kuwait", "Kuwait City", 29.3759, 47.9774],
  "Asia/Bahrain": ["Bahrain", "Manama", 26.2285, 50.586],
  "Asia/Muscat": ["Oman", "Muscat", 23.588, 58.3829],
  "Asia/Tehran": ["Iran", "Tehran", 35.6892, 51.389],
  "Asia/Baghdad": ["Iraq", "Baghdad", 33.3152, 44.3661],
  "Asia/Jerusalem": ["Israel", "Jerusalem", 31.7683, 35.2137],
  "Asia/Amman": ["Jordan", "Amman", 31.9454, 35.9284],
  "Asia/Beirut": ["Lebanon", "Beirut", 33.8938, 35.5018],
  "Asia/Istanbul": ["Turkey", "Istanbul", 41.0082, 28.9784],
  "Europe/Istanbul": ["Turkey", "Istanbul", 41.0082, 28.9784],
  // ---- Europe ----
  "Europe/London": ["United Kingdom", "London", 51.5074, -0.1278],
  "Europe/Dublin": ["Ireland", "Dublin", 53.3498, -6.2603],
  "Europe/Paris": ["France", "Paris", 48.8566, 2.3522],
  "Europe/Madrid": ["Spain", "Madrid", 40.4168, -3.7038],
  "Europe/Lisbon": ["Portugal", "Lisbon", 38.7223, -9.1393],
  "Europe/Berlin": ["Germany", "Berlin", 52.52, 13.405],
  "Europe/Amsterdam": ["Netherlands", "Amsterdam", 52.3676, 4.9041],
  "Europe/Brussels": ["Belgium", "Brussels", 50.8503, 4.3517],
  "Europe/Zurich": ["Switzerland", "Zurich", 47.3769, 8.5417],
  "Europe/Rome": ["Italy", "Rome", 41.9028, 12.4964],
  "Europe/Vienna": ["Austria", "Vienna", 48.2082, 16.3738],
  "Europe/Prague": ["Czechia", "Prague", 50.0755, 14.4378],
  "Europe/Warsaw": ["Poland", "Warsaw", 52.2297, 21.0122],
  "Europe/Stockholm": ["Sweden", "Stockholm", 59.3293, 18.0686],
  "Europe/Oslo": ["Norway", "Oslo", 59.9139, 10.7522],
  "Europe/Copenhagen": ["Denmark", "Copenhagen", 55.6761, 12.5683],
  "Europe/Helsinki": ["Finland", "Helsinki", 60.1699, 24.9384],
  "Europe/Athens": ["Greece", "Athens", 37.9838, 23.7275],
  "Europe/Bucharest": ["Romania", "Bucharest", 44.4268, 26.1025],
  "Europe/Budapest": ["Hungary", "Budapest", 47.4979, 19.0402],
  "Europe/Kyiv": ["Ukraine", "Kyiv", 50.4501, 30.5234],
  "Europe/Kiev": ["Ukraine", "Kyiv", 50.4501, 30.5234],
  "Europe/Moscow": ["Russia", "Moscow", 55.7558, 37.6173],
  // ---- North America ----
  "America/New_York": ["United States of America", "New York", 40.7128, -74.006],
  "America/Detroit": ["United States of America", "Detroit", 42.3314, -83.0458],
  "America/Chicago": ["United States of America", "Chicago", 41.8781, -87.6298],
  "America/Denver": ["United States of America", "Denver", 39.7392, -104.9903],
  "America/Phoenix": ["United States of America", "Phoenix", 33.4484, -112.074],
  "America/Los_Angeles": ["United States of America", "Los Angeles", 34.0522, -118.2437],
  "America/Anchorage": ["United States of America", "Anchorage", 61.2181, -149.9003],
  "Pacific/Honolulu": ["United States of America", "Honolulu", 21.3069, -157.8583],
  "America/Toronto": ["Canada", "Toronto", 43.6532, -79.3832],
  "America/Vancouver": ["Canada", "Vancouver", 49.2827, -123.1207],
  "America/Edmonton": ["Canada", "Calgary", 51.0447, -114.0719],
  "America/Winnipeg": ["Canada", "Winnipeg", 49.8951, -97.1384],
  "America/Halifax": ["Canada", "Halifax", 44.6488, -63.5752],
  "America/Mexico_City": ["Mexico", "Mexico City", 19.4326, -99.1332],
  // ---- Latin America ----
  "America/Sao_Paulo": ["Brazil", "São Paulo", -23.5505, -46.6333],
  "America/Argentina/Buenos_Aires": ["Argentina", "Buenos Aires", -34.6037, -58.3816],
  "America/Santiago": ["Chile", "Santiago", -33.4489, -70.6693],
  "America/Bogota": ["Colombia", "Bogotá", 4.711, -74.0721],
  "America/Lima": ["Peru", "Lima", -12.0464, -77.0428],
  "America/Guayaquil": ["Ecuador", "Guayaquil", -2.1894, -79.8891],
  "America/Caracas": ["Venezuela", "Caracas", 10.4806, -66.9036],
  // ---- Africa ----
  "Africa/Cairo": ["Egypt", "Cairo", 30.0444, 31.2357],
  "Africa/Lagos": ["Nigeria", "Lagos", 6.5244, 3.3792],
  "Africa/Nairobi": ["Kenya", "Nairobi", -1.2921, 36.8219],
  "Africa/Johannesburg": ["South Africa", "Johannesburg", -26.2041, 28.0473],
  "Africa/Accra": ["Ghana", "Accra", 5.6037, -0.187],
  "Africa/Casablanca": ["Morocco", "Casablanca", 33.5731, -7.5898],
  "Africa/Addis_Ababa": ["Ethiopia", "Addis Ababa", 9.03, 38.74],
  "Africa/Dar_es_Salaam": ["Tanzania", "Dar es Salaam", -6.7924, 39.2083],
  // ---- Oceania ----
  "Australia/Sydney": ["Australia", "Sydney", -33.8688, 151.2093],
  "Australia/Melbourne": ["Australia", "Melbourne", -37.8136, 144.9631],
  "Australia/Brisbane": ["Australia", "Brisbane", -27.4698, 153.0251],
  "Australia/Adelaide": ["Australia", "Adelaide", -34.9285, 138.6007],
  "Australia/Perth": ["Australia", "Perth", -31.9523, 115.8613],
  "Pacific/Auckland": ["New Zealand", "Auckland", -36.8485, 174.7633],
  "Pacific/Fiji": ["Fiji", "Suva", -18.1248, 178.4501],
};
