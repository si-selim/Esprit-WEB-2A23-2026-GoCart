
const AI_PROXY = 'ai_proxy.php';
const OSRM_API = 'https://router.project-osrm.org/route/v1/driving';
const AVG_SPEED_KMH = 10;

let map = null;
let cityCenter = null;
let cityBounds = null;
let cityZoom = 14;
let markers = [];   // { type:'depart'|'arrivee'|'waypoint', latlng, marker }
let polyline = null;
let currentCity = '';
let _regionCenters = {};
let _iaRoutesCache = null;
let _aiSuggestionsCache = { depart: null, arrivee: null };
let _aiSuggestionsLoading = { depart: false, arrivee: false };

const CITY_PLACES = {
    'Ariana': [
        { nom: 'Ariana Ville', type: 'place', quartier: 'Centre Ariana' },
        { nom: 'Raoued', type: 'place', quartier: 'Raoued' },
        { nom: 'Raoued Plage', type: 'corniche', quartier: 'Raoued' },
        { nom: 'Mnihla', type: 'place', quartier: 'Mnihla' },
        { nom: 'Ettadhamen', type: 'place', quartier: 'Ettadhamen' },
        { nom: 'La Soukra', type: 'avenue', quartier: 'La Soukra' },
        { nom: 'Soukra', type: 'avenue', quartier: 'La Soukra' },
        { nom: 'Borj Louzir', type: 'parc', quartier: 'Borj Louzir' },
        { nom: 'Ennasr 1', type: 'avenue', quartier: 'Ennasr' },
        { nom: 'Ennasr 2', type: 'avenue', quartier: 'Ennasr' },
        { nom: 'Cité Ennasr', type: 'avenue', quartier: 'Ennasr' },
        { nom: 'Technopole El Ghazela', type: 'parc', quartier: 'Ghazela' },
        { nom: 'El Ghazela', type: 'parc', quartier: 'Ghazela' },
        { nom: 'Kalaat El Andalous', type: 'place', quartier: 'Kalaat El Andalous' },
        { nom: 'Sidi Thabet', type: 'place', quartier: 'Sidi Thabet' },
        { nom: 'Centre Ariana', type: 'place', quartier: 'Centre Ariana' },
    ],
    'Tunis': [
        { nom: 'Avenue Habib Bourguiba', type: 'avenue', quartier: 'Centre Tunis' },
        { nom: 'Médina de Tunis', type: 'medina', quartier: 'Médina' },
        { nom: 'La Marsa', type: 'corniche', quartier: 'La Marsa' },
        { nom: 'Corniche La Marsa', type: 'corniche', quartier: 'La Marsa' },
        { nom: 'Carthage', type: 'monument', quartier: 'Carthage' },
        { nom: 'Sidi Bou Saïd', type: 'monument', quartier: 'Sidi Bou Saïd' },
        { nom: 'Lac de Tunis', type: 'parc', quartier: 'Les Berges du Lac' },
        { nom: 'Lac 1', type: 'parc', quartier: 'Les Berges du Lac' },
        { nom: 'Lac 2', type: 'parc', quartier: 'Les Berges du Lac' },
        { nom: 'Les Berges du Lac', type: 'parc', quartier: 'Les Berges du Lac' },
        { nom: 'Bardo', type: 'monument', quartier: 'Le Bardo' },
        { nom: 'Musée du Bardo', type: 'monument', quartier: 'Le Bardo' },
        { nom: 'Le Bardo', type: 'place', quartier: 'Le Bardo' },
        { nom: 'El Manar', type: 'parc', quartier: 'El Manar' },
        { nom: 'Belvedere', type: 'parc', quartier: 'Belvedere' },
        { nom: 'Montplaisir', type: 'avenue', quartier: 'Montplaisir' },
        { nom: 'El Menzah', type: 'parc', quartier: 'El Menzah' },
        { nom: 'Menzah', type: 'parc', quartier: 'El Menzah' },
        { nom: 'Ennasr', type: 'avenue', quartier: 'Ennasr' },
        { nom: 'La Goulette', type: 'corniche', quartier: 'La Goulette' },
        { nom: 'Hammam Lif', type: 'corniche', quartier: 'Hammam Lif' },
        { nom: 'Radès', type: 'stade', quartier: 'Radès' },
        { nom: 'Tunis Centre', type: 'place', quartier: 'Centre Tunis' },
        { nom: 'Sidi Hassine', type: 'place', quartier: 'Sidi Hassine' },
        { nom: 'El Omrane', type: 'place', quartier: 'El Omrane' },
        { nom: 'El Kabaria', type: 'place', quartier: 'El Kabaria' },
        { nom: 'Ettahrir', type: 'place', quartier: 'Ettahrir' },
    ],
    'Ben Arous': [
        { nom: 'Ben Arous Centre', type: 'place', quartier: 'Centre' },
        { nom: 'Radès', type: 'stade', quartier: 'Radès' },
        { nom: 'Port de Radès', type: 'corniche', quartier: 'Radès' },
        { nom: 'Hammam Lif', type: 'corniche', quartier: 'Hammam Lif' },
        { nom: 'Plage Hammam Lif', type: 'corniche', quartier: 'Hammam Lif' },
        { nom: 'Hammam Chott', type: 'place', quartier: 'Hammam Chott' },
        { nom: 'Ezzahra', type: 'place', quartier: 'Ezzahra' },
        { nom: 'Mégrine', type: 'place', quartier: 'Mégrine' },
        { nom: 'Megrine', type: 'place', quartier: 'Mégrine' },
        { nom: 'Mornag', type: 'parc', quartier: 'Mornag' },
        { nom: 'Mornag Agricole', type: 'parc', quartier: 'Mornag' },
        { nom: 'Fouchana', type: 'place', quartier: 'Fouchana' },
        { nom: 'Zone Industrielle Fouchana', type: 'place', quartier: 'Fouchana' },
    ],
    'Manouba': [
        { nom: 'Manouba Centre', type: 'place', quartier: 'Centre' },
        { nom: 'Den Den', type: 'parc', quartier: 'Den Den' },
        { nom: 'Den Den Parc', type: 'parc', quartier: 'Den Den' },
        { nom: 'Université de Manouba', type: 'monument', quartier: 'Manouba' },
        { nom: 'Oued Ellil', type: 'place', quartier: 'Oued Ellil' },
        { nom: 'Oued Ellil Centre', type: 'place', quartier: 'Oued Ellil' },
        { nom: 'Douar Hicher', type: 'place', quartier: 'Douar Hicher' },
        { nom: 'Tebourba', type: 'place', quartier: 'Tébourba' },
        { nom: 'Tébourba', type: 'place', quartier: 'Tébourba' },
        { nom: 'Tebourba Historique', type: 'monument', quartier: 'Tébourba' },
        { nom: 'Jedaida', type: 'place', quartier: 'Jedaida' },
    ],
    'Nabeul': [
        { nom: 'Nabeul Centre', type: 'place', quartier: 'Centre Nabeul' },
        { nom: 'Corniche de Nabeul', type: 'corniche', quartier: 'Front de mer' },
        { nom: 'Hammamet', type: 'corniche', quartier: 'Hammamet' },
        { nom: 'Hammamet Nord', type: 'parc', quartier: 'Hammamet Nord' },
        { nom: 'Hammamet Yasmine', type: 'corniche', quartier: 'Hammamet' },
        { nom: 'Marina Hammamet', type: 'corniche', quartier: 'Hammamet' },
        { nom: 'Kelibia', type: 'monument', quartier: 'Kelibia' },
        { nom: 'Plage Kelibia', type: 'corniche', quartier: 'Kelibia' },
        { nom: 'El Haouaria', type: 'parc', quartier: 'El Haouaria' },
        { nom: 'Korba', type: 'corniche', quartier: 'Korba' },
        { nom: 'Maamoura', type: 'parc', quartier: 'Maamoura' },
        { nom: 'Grombalia', type: 'place', quartier: 'Grombalia' },
        { nom: 'Grombalia Centre', type: 'place', quartier: 'Grombalia' },
        { nom: 'Beni Khiar', type: 'place', quartier: 'Béni Khiar' },
        { nom: 'Béni Khiar', type: 'place', quartier: 'Béni Khiar' },
        { nom: 'Beni Khiar Plage', type: 'corniche', quartier: 'Béni Khiar' },
        { nom: 'Menzel Temime', type: 'place', quartier: 'Menzel Temime' },
        { nom: 'Soliman', type: 'place', quartier: 'Soliman' },
        { nom: 'Bou Argoub', type: 'place', quartier: 'Bou Argoub' },
        { nom: 'Dar Chaabane El Fehri', type: 'place', quartier: 'Dar Chaâbane' },
        { nom: 'Dar Chaâbane El Fehri', type: 'place', quartier: 'Dar Chaâbane' },
        { nom: 'Takelsa', type: 'place', quartier: 'Takelsa' },
    ],
    'Bizerte': [
        { nom: 'Vieux Port de Bizerte', type: 'corniche', quartier: 'Centre Bizerte' },
        { nom: 'Corniche de Bizerte', type: 'corniche', quartier: 'Front de mer' },
        { nom: 'Port de Bizerte', type: 'corniche', quartier: 'Centre Bizerte' },
        { nom: 'Lac de Bizerte', type: 'parc', quartier: 'Lac' },
        { nom: 'Lac Ichkeul', type: 'parc', quartier: 'Ichkeul' },
        { nom: 'Zarzouna', type: 'place', quartier: 'Zarzouna' },
        { nom: 'Menzel Bourguiba', type: 'place', quartier: 'Menzel Bourguiba' },
        { nom: 'Ras Angela', type: 'monument', quartier: 'Ras Angela' },
        { nom: 'Ras Jebel', type: 'corniche', quartier: 'Ras Jebel' },
        { nom: 'Ras Jebel Plage', type: 'corniche', quartier: 'Ras Jebel' },
        { nom: 'Sidi Ali Mekki', type: 'corniche', quartier: 'Sidi Ali Mekki' },
        { nom: 'Mateur', type: 'place', quartier: 'Mateur' },
        { nom: 'Mateur Centre', type: 'place', quartier: 'Mateur' },
        { nom: 'Sejnane', type: 'place', quartier: 'Sejnane' },
        { nom: 'Utique', type: 'monument', quartier: 'Utique' },
        { nom: 'Ghar El Melh', type: 'corniche', quartier: 'Ghar El Melh' },
        { nom: 'Bizerte Nord', type: 'place', quartier: 'Bizerte Nord' },
        { nom: 'Bizerte Sud', type: 'place', quartier: 'Bizerte Sud' },
        { nom: 'Jebel Kebir Fort', type: 'monument', quartier: 'Jebel Kebir' },
        { nom: 'Borj Challouf', type: 'monument', quartier: 'Bizerte' },
    ],
    'Sousse': [
        { nom: 'Boujaafar', type: 'parc', quartier: 'Sousse Centre' },
        { nom: 'Port El Kantaoui', type: 'corniche', quartier: 'Nord Sousse' },
        { nom: 'Médina de Sousse', type: 'medina', quartier: 'Sousse Médina' },
        { nom: 'Sahloul', type: 'avenue', quartier: 'Sahloul' },
        { nom: 'Hammam Sousse', type: 'parc', quartier: 'Hammam Sousse' },
        { nom: 'Kantaoui Marina', type: 'corniche', quartier: 'Port El Kantaoui' },
        { nom: 'Avenue Habib Bourguiba Sousse', type: 'avenue', quartier: 'Centre Sousse' },
        { nom: 'Corniche de Sousse', type: 'corniche', quartier: 'Front de mer' },
        { nom: 'Stade Olympique de Sousse', type: 'stade', quartier: 'Khezama' },
        { nom: 'Khezama', type: 'parc', quartier: 'Khezama' },
        { nom: 'Msaken', type: 'place', quartier: 'Msaken' },
        { nom: 'Msaken Centre', type: 'place', quartier: 'Msaken' },
        { nom: 'Enfidha', type: 'place', quartier: 'Enfidha' },
        { nom: 'Enfidha Aéroport', type: 'place', quartier: 'Enfidha' },
        { nom: 'Hergla', type: 'corniche', quartier: 'Hergla' },
        { nom: 'Akouda', type: 'place', quartier: 'Akouda' },
        { nom: 'Place Farhat Hached', type: 'place', quartier: 'Centre Sousse' },
        { nom: 'Kalaa Kebira', type: 'place', quartier: 'Kalaa Kebira' },
        { nom: 'Kalaa Seghira', type: 'place', quartier: 'Kalaa Seghira' },
        { nom: 'Sousse Centre', type: 'place', quartier: 'Centre Sousse' },
    ],
    'Monastir': [
        { nom: 'Ribat de Monastir', type: 'monument', quartier: 'Médina' },
        { nom: 'Ribat Monastir', type: 'monument', quartier: 'Médina' },
        { nom: 'Corniche de Monastir', type: 'corniche', quartier: 'Front de mer' },
        { nom: 'Médina de Monastir', type: 'medina', quartier: 'Médina' },
        { nom: 'Marina Monastir', type: 'corniche', quartier: 'Marina' },
        { nom: 'Skanes', type: 'parc', quartier: 'Skanes' },
        { nom: 'Khniss', type: 'corniche', quartier: 'Khniss' },
        { nom: 'Ksibet El Mediouni', type: 'place', quartier: 'Ksibet El Mediouni' },
        { nom: 'Ksibet el-Mediouni', type: 'place', quartier: 'Ksibet el-Mediouni' },
        { nom: 'Ouerdanine', type: 'place', quartier: 'Ouerdanine' },
        { nom: 'Stade Mustapha Ben Jannet', type: 'stade', quartier: 'Monastir Centre' },
        { nom: 'Moknine', type: 'place', quartier: 'Moknine' },
        { nom: 'Moknine Centre', type: 'place', quartier: 'Moknine' },
        { nom: 'Jemmal', type: 'place', quartier: 'Jemmal' },
        { nom: 'Sahline', type: 'place', quartier: 'Sahline' },
        { nom: 'Beni Hassen', type: 'place', quartier: 'Beni Hassen' },
        { nom: 'Plage Monastir', type: 'corniche', quartier: 'Monastir' },
    ],
    'Mahdia': [
        { nom: 'Médina de Mahdia', type: 'medina', quartier: 'Médina' },
        { nom: 'Cap Afrique', type: 'corniche', quartier: 'Cap Afrique' },
        { nom: 'Corniche de Mahdia', type: 'corniche', quartier: 'Front de mer' },
        { nom: 'Plage Mahdia', type: 'corniche', quartier: 'Mahdia' },
        { nom: 'El Jem', type: 'monument', quartier: 'El Jem' },
        { nom: 'Amphithéâtre El Jem', type: 'monument', quartier: 'El Jem' },
        { nom: 'Chebba', type: 'place', quartier: 'Chebba' },
        { nom: 'Ksour Essef', type: 'place', quartier: 'Ksour Essef' },
        { nom: 'Rejiche', type: 'place', quartier: 'Rejiche' },
        { nom: 'Bou Merdes', type: 'place', quartier: 'Bou Merdes' },
        { nom: 'Mahdia Centre', type: 'place', quartier: 'Centre Mahdia' },
    ],
    'Sfax': [
        { nom: 'Médina de Sfax', type: 'medina', quartier: 'Médina' },
        { nom: 'Médina Sfax', type: 'medina', quartier: 'Médina' },
        { nom: 'Corniche de Sfax', type: 'corniche', quartier: 'Front de mer' },
        { nom: 'Port de Sfax', type: 'corniche', quartier: 'Port Sfax' },
        { nom: 'Avenue Habib Bourguiba Sfax', type: 'avenue', quartier: 'Centre Sfax' },
        { nom: 'Sakiet Ezzit', type: 'place', quartier: 'Sakiet Ezzit' },
        { nom: 'Sakiet Eddaier', type: 'place', quartier: 'Sakiet Eddaier' },
        { nom: 'Thyna', type: 'place', quartier: 'Thyna' },
        { nom: 'El Ain', type: 'parc', quartier: 'El Ain' },
        { nom: 'Gremda', type: 'parc', quartier: 'Gremda' },
        { nom: 'Stade Taieb Mhiri', type: 'stade', quartier: 'Sfax Centre' },
        { nom: 'Kerkennah', type: 'corniche', quartier: 'Kerkennah' },
        { nom: 'Kerkennah Îles', type: 'corniche', quartier: 'Kerkennah' },
        { nom: 'Jebeniana', type: 'place', quartier: 'Jebeniana' },
        { nom: 'Zone Industrielle Sfax', type: 'place', quartier: 'Zone Industrielle' },
        { nom: 'Sfax Centre', type: 'place', quartier: 'Centre Sfax' },
    ],
    'Kairouan': [
        { nom: 'Grande Mosquée de Kairouan', type: 'monument', quartier: 'Médina' },
        { nom: 'Grande Mosquée', type: 'monument', quartier: 'Médina' },
        { nom: 'Médina de Kairouan', type: 'medina', quartier: 'Médina' },
        { nom: 'Bassins Aghlabides', type: 'monument', quartier: 'Centre' },
        { nom: 'Aghlabid Basins', type: 'monument', quartier: 'Centre' },
        { nom: 'Centre Historique Kairouan', type: 'medina', quartier: 'Médina' },
        { nom: 'Avenue de la République Kairouan', type: 'avenue', quartier: 'Centre' },
        { nom: 'Oueslatia', type: 'place', quartier: 'Oueslatia' },
        { nom: 'Haffouz', type: 'place', quartier: 'Haffouz' },
        { nom: 'Sbikha', type: 'place', quartier: 'Sbikha' },
        { nom: 'Bou Hajla', type: 'place', quartier: 'Bou Hajla' },
        { nom: 'Nasrallah', type: 'place', quartier: 'Nasrallah' },
        { nom: 'Kairouan Centre', type: 'place', quartier: 'Centre Kairouan' },
    ],
    'Kasserine': [
        { nom: 'Kasserine Centre', type: 'place', quartier: 'Centre' },
        { nom: 'Sbeitla', type: 'monument', quartier: 'Sbeitla' },
        { nom: 'Site Romain Sbeitla', type: 'monument', quartier: 'Sbeitla' },
        { nom: 'Djebel Chambi', type: 'parc', quartier: 'Chambi' },
        { nom: 'Thala', type: 'place', quartier: 'Thala' },
        { nom: 'Montagnes Thala', type: 'parc', quartier: 'Thala' },
        { nom: 'Fériana', type: 'place', quartier: 'Fériana' },
        { nom: 'Foussana', type: 'place', quartier: 'Foussana' },
        { nom: 'Haidra', type: 'monument', quartier: 'Haidra' },
    ],
    'Sidi Bouzid': [
        { nom: 'Sidi Bouzid Centre', type: 'place', quartier: 'Centre' },
        { nom: 'Regueb', type: 'place', quartier: 'Regueb' },
        { nom: 'Meknassy', type: 'place', quartier: 'Meknassy' },
        { nom: 'Jelma', type: 'place', quartier: 'Jelma' },
        { nom: 'Bir El Hafey', type: 'place', quartier: 'Bir El Hafey' },
        { nom: 'Zones Agricoles Sidi Bouzid', type: 'parc', quartier: 'Périphérie' },
    ],
    'Gabès': [
        { nom: 'Oasis de Gabès', type: 'parc', quartier: 'Centre Gabès' },
        { nom: 'Oasis Gabès', type: 'parc', quartier: 'Centre Gabès' },
        { nom: 'Corniche de Gabès', type: 'corniche', quartier: 'Front de mer' },
        { nom: 'Plage Gabès', type: 'corniche', quartier: 'Gabès' },
        { nom: 'Chenini Gabès', type: 'medina', quartier: 'Chenini' },
        { nom: 'Matmata', type: 'monument', quartier: 'Matmata' },
        { nom: 'Matmata Troglodytes', type: 'monument', quartier: 'Matmata' },
        { nom: 'El Hamma', type: 'place', quartier: 'El Hamma' },
        { nom: 'Mareth', type: 'place', quartier: 'Mareth' },
        { nom: 'Ghannouch', type: 'place', quartier: 'Ghannouch' },
        { nom: 'Gabès Centre', type: 'place', quartier: 'Centre Gabès' },
    ],
    'Médenine': [
        { nom: 'Médenine Centre', type: 'place', quartier: 'Centre' },
        { nom: 'Ksar Ouled Soltane', type: 'monument', quartier: 'Désert' },
        { nom: 'Djerba', type: 'corniche', quartier: 'Djerba' },
        { nom: 'Djerba Plages', type: 'corniche', quartier: 'Djerba' },
        { nom: 'Houmt Souk', type: 'place', quartier: 'Houmt Souk' },
        { nom: 'Houmt Souk Port', type: 'corniche', quartier: 'Houmt Souk' },
        { nom: 'Midoun', type: 'place', quartier: 'Midoun' },
        { nom: 'Zarzis', type: 'corniche', quartier: 'Zarzis' },
        { nom: 'Zarzis Touristique', type: 'corniche', quartier: 'Zarzis' },
        { nom: 'Ben Gardane', type: 'place', quartier: 'Ben Gardane' },
    ],
    'Tataouine': [
        { nom: 'Tataouine Centre', type: 'place', quartier: 'Centre' },
        { nom: 'Ksar Hadada', type: 'monument', quartier: 'Désert' },
        { nom: 'Ksour Tataouine', type: 'monument', quartier: 'Désert' },
        { nom: 'Chenini Tataouine', type: 'monument', quartier: 'Montagne' },
        { nom: 'Ghomrassen', type: 'place', quartier: 'Ghomrassen' },
        { nom: 'Remada', type: 'place', quartier: 'Remada' },
        { nom: 'Bir Lahmar', type: 'place', quartier: 'Bir Lahmar' },
        { nom: 'Dehiba', type: 'place', quartier: 'Dehiba' },
        { nom: 'Désert Tataouine', type: 'parc', quartier: 'Désert' },
        { nom: 'Montagnes Tataouine', type: 'parc', quartier: 'Montagne' },
    ],
    'Gafsa': [
        { nom: 'Gafsa Centre', type: 'place', quartier: 'Centre' },
        { nom: 'Centre de Gafsa', type: 'place', quartier: 'Centre' },
        { nom: 'Oasis de Gafsa', type: 'parc', quartier: 'Gafsa Oasis' },
        { nom: 'Oasis Gafsa', type: 'parc', quartier: 'Gafsa Oasis' },
        { nom: 'Stade Municipal Gafsa', type: 'stade', quartier: 'Gafsa Centre' },
        { nom: 'Redeyef', type: 'place', quartier: 'Redeyef' },
        { nom: 'Metlaoui', type: 'place', quartier: 'Metlaoui' },
        { nom: 'Mines Metlaoui', type: 'monument', quartier: 'Metlaoui' },
        { nom: 'Mdhilla', type: 'place', quartier: 'Mdhilla' },
        { nom: 'Sened', type: 'place', quartier: 'Sened' },
    ],
    'Tozeur': [
        { nom: 'Médina de Tozeur', type: 'medina', quartier: 'Centre Tozeur' },
        { nom: 'Palmeraie de Tozeur', type: 'parc', quartier: 'Palmeraie' },
        { nom: 'Oasis Tozeur', type: 'parc', quartier: 'Palmeraie' },
        { nom: 'Dar Cheraït', type: 'monument', quartier: 'Centre Tozeur' },
        { nom: 'Chott El Jerid', type: 'monument', quartier: 'Désert' },
        { nom: 'Chott el-Jérid', type: 'monument', quartier: 'Désert' },
        { nom: 'Nefta', type: 'place', quartier: 'Nefta' },
        { nom: 'Tozeur Centre', type: 'avenue', quartier: 'Centre' },
        { nom: 'Degache', type: 'place', quartier: 'Degache' },
        { nom: 'Tamaghza', type: 'monument', quartier: 'Tamaghza' },
        { nom: 'Chebika', type: 'monument', quartier: 'Chebika' },
    ],
    'Kébili': [
        { nom: 'Kébili Centre', type: 'place', quartier: 'Centre' },
        { nom: 'Douz', type: 'monument', quartier: 'Douz' },
        { nom: 'Douz Porte du Désert', type: 'monument', quartier: 'Douz' },
        { nom: 'Palmeraie de Kébili', type: 'parc', quartier: 'Palmeraie' },
        { nom: 'Oasis Kébili', type: 'parc', quartier: 'Palmeraie' },
        { nom: 'Désert Sahara Kébili', type: 'parc', quartier: 'Désert' },
        { nom: 'Souk Lahad', type: 'place', quartier: 'Souk Lahad' },
        { nom: 'El Faouar', type: 'place', quartier: 'El Faouar' },
    ],
    'Zaghouan': [
        { nom: 'Zaghouan Centre', type: 'place', quartier: 'Centre' },
        { nom: 'Temple des Eaux', type: 'monument', quartier: 'Zaghouan' },
        { nom: 'Djebel Zaghouan', type: 'parc', quartier: 'Zaghouan' },
        { nom: 'Mont Zaghouan', type: 'parc', quartier: 'Zaghouan' },
        { nom: 'El Fahs', type: 'place', quartier: 'El Fahs' },
        { nom: 'Bir Mcherga', type: 'place', quartier: 'Bir Mcherga' },
        { nom: 'Zriba', type: 'monument', quartier: 'Zriba' },
    ],
    'Béja': [
        { nom: 'Béja Centre', type: 'place', quartier: 'Centre' },
        { nom: 'Oued Béja', type: 'parc', quartier: 'Oued Béja' },
        { nom: 'Testour', type: 'monument', quartier: 'Testour' },
        { nom: 'Ville Historique Testour', type: 'monument', quartier: 'Testour' },
        { nom: 'Medjez El Bab', type: 'place', quartier: 'Medjez El Bab' },
        { nom: 'Barrage Sidi Salem', type: 'parc', quartier: 'Sidi Salem' },
        { nom: 'Nefza', type: 'place', quartier: 'Nefza' },
        { nom: 'Teboursouk', type: 'place', quartier: 'Teboursouk' },
    ],
    'Jendouba': [
        { nom: 'Jendouba Centre', type: 'place', quartier: 'Centre' },
        { nom: 'Bulla Regia', type: 'monument', quartier: 'Bulla Regia' },
        { nom: 'Ain Draham', type: 'parc', quartier: 'Ain Draham' },
        { nom: 'Aïn Draham', type: 'parc', quartier: 'Ain Draham' },
        { nom: 'Forêt Ain Draham', type: 'parc', quartier: 'Ain Draham' },
        { nom: 'Tabarka', type: 'corniche', quartier: 'Tabarka' },
        { nom: 'Plage Tabarka', type: 'corniche', quartier: 'Tabarka' },
        { nom: 'Corail Tabarka', type: 'corniche', quartier: 'Tabarka' },
        { nom: 'Bou Salem', type: 'place', quartier: 'Bou Salem' },
    ],
    'Kef': [
        { nom: 'Le Kef Centre', type: 'place', quartier: 'Centre' },
        { nom: 'Citadelle du Kef', type: 'monument', quartier: 'Médina' },
        { nom: 'Fort Kef', type: 'monument', quartier: 'Médina' },
        { nom: 'Sakiet Sidi Youssef', type: 'place', quartier: 'Sakiet' },
        { nom: 'Tajerouine', type: 'place', quartier: 'Tajerouine' },
        { nom: 'Dahmani', type: 'place', quartier: 'Dahmani' },
        { nom: 'Nebeur', type: 'place', quartier: 'Nebeur' },
        { nom: 'Montagnes Le Kef', type: 'parc', quartier: 'Montagne' },
    ],
    'Siliana': [
        { nom: 'Siliana Centre', type: 'place', quartier: 'Centre' },
        { nom: 'Makthar', type: 'monument', quartier: 'Makthar' },
        { nom: 'Site Romain Makthar', type: 'monument', quartier: 'Makthar' },
        { nom: 'Bou Arada', type: 'place', quartier: 'Bou Arada' },
        { nom: 'Gaafour', type: 'place', quartier: 'Gaafour' },
        { nom: 'Zones Montagneuses Siliana', type: 'parc', quartier: 'Montagne' },
    ],
};

/* ═══════════════════════════════════════
   LOGIQUE MULTI-RÉGION
═══════════════════════════════════════ */
function getRegionParts() {
    return currentCity.split(/[-,]/).map(s => s.trim()).filter(Boolean);
}
function isMultiRegion() { return getRegionParts().length > 1; }

function getPlacesForDepart() {
    const parts = getRegionParts();
    const key = Object.keys(CITY_PLACES).find(k => k.toLowerCase() === parts[0].toLowerCase());
    return key ? CITY_PLACES[key] : [];
}
function getPlacesForArrivee() {
    const parts = getRegionParts();
    let allPlaces = [];
    parts.forEach(part => {
        const key = Object.keys(CITY_PLACES).find(k => k.toLowerCase() === part.toLowerCase());
        if (key) allPlaces = allPlaces.concat(CITY_PLACES[key]);
    });
    return allPlaces;
}
function filterPlaces(query, type) {
    const places = type === 'arrivee' ? getPlacesForArrivee() : getPlacesForDepart();
    if (!query || query.trim().length === 0) return places.slice(0, 8);
    const q = query.toLowerCase().trim();
    return places.filter(p =>
        p.nom.toLowerCase().includes(q) || (p.quartier && p.quartier.toLowerCase().includes(q))
    ).slice(0, 8);
}

/* ═══════════════════════════════════════
   INIT MAP
═══════════════════════════════════════ */
async function initMap(cityName) {
    currentCity = cityName || 'Tunis';
    _aiSuggestionsCache = { depart: null, arrivee: null };
    _aiSuggestionsLoading = { depart: false, arrivee: false };
    _regionCenters = {};

    const cityParts = getRegionParts();
    const allCenters = [];
    for (const part of cityParts) {
        try {
            const r = await fetch(`https://geocoding-api.open-meteo.com/v1/search?name=${encodeURIComponent(part)}&count=1&language=fr&format=json`);
            const d = await r.json();
            if (d.results && d.results[0]) {
                const c = [d.results[0].latitude, d.results[0].longitude];
                allCenters.push(c);
                _regionCenters[part] = c;
            }
        } catch(e){}
    }
    if (allCenters.length === 0) allCenters.push([36.8065, 10.1815]);
    cityCenter = allCenters[0];

    if (map) { map.remove(); map = null; }
    map = L.map('parcours-map', { center: cityCenter, zoom: cityZoom, zoomControl: true, attributionControl: false });
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

    if (allCenters.length > 1) {
        const lats = allCenters.map(c => c[0]);
        const lngs = allCenters.map(c => c[1]);
        cityBounds = L.latLngBounds(
            [Math.min(...lats) - 0.50, Math.min(...lngs) - 0.60],
            [Math.max(...lats) + 0.50, Math.max(...lngs) + 0.60]
        );
        map.setView([lats.reduce((a,b)=>a+b,0)/lats.length, lngs.reduce((a,b)=>a+b,0)/lngs.length], cityParts.length >= 3 ? 8 : 9);
    } else {
        cityBounds = L.latLngBounds(
            [cityCenter[0]-0.50, cityCenter[1]-0.60],
            [cityCenter[0]+0.50, cityCenter[1]+0.60]
        );
    }
    map.setMaxBounds(cityBounds.pad(0.1));
    map.on('click', onMapClick);

    updateMapInstructions();
    autoPlaceExistingPoints();
    initAutocomplete('point_depart', 'depart');
    initAutocomplete('point_arrivee', 'arrivee');
    updateTimeDisplay(null);
    injectNavButton();
}

/* ═══════════════════════════════════════
   TEMPS ESTIMÉ
═══════════════════════════════════════ */
function updateTimeDisplay(distKm) {
    const display = document.getElementById('map-time-display');
    const waiting = document.getElementById('map-time-waiting');
    if (!display) return;
    if (distKm === null || distKm === undefined) {
        display.textContent = '—';
        if (waiting) waiting.style.display = 'inline';
    } else {
        const tempsMin = Math.round((distKm / AVG_SPEED_KMH) * 60);
        const tempsStr = tempsMin >= 60
            ? `${Math.floor(tempsMin/60)}h ${tempsMin%60 > 0 ? tempsMin%60+'min' : ''}`
            : `${tempsMin} min`;
        display.textContent = tempsStr;
        if (waiting) waiting.style.display = 'none';
    }
}

/* ═══════════════════════════════════════
   AUTOCOMPLETE
═══════════════════════════════════════ */
function initAutocomplete(inputId, type) {
    const input = document.getElementById(inputId);
    if (!input) return;

    let dropdown = document.getElementById(inputId + '_dropdown');
    if (!dropdown) {
        dropdown = document.createElement('div');
        dropdown.id = inputId + '_dropdown';
        dropdown.style.cssText = `position:absolute;z-index:9999;background:white;border:1px solid #e2e8f0;border-radius:14px;box-shadow:0 12px 32px rgba(16,42,67,.16);max-height:340px;overflow-y:auto;width:100%;top:calc(100% + 4px);left:0;display:none;`;
        input.parentElement.style.position = 'relative';
        input.parentElement.appendChild(dropdown);
    }

    let _searchTimer = null;
    input.addEventListener('focus', () => {
        showLocalSuggestions(input.value.trim(), dropdown, input, type);
    });
    input.addEventListener('input', () => {
        clearTimeout(_searchTimer);
        const val = input.value.trim();
        if (val.length === 0) clearMarkerByType(type);
        _searchTimer = setTimeout(() => showLocalSuggestions(val, dropdown, input, type), 200);
    });
    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
}

function clearMarkerByType(type) {
    const idx = markers.findIndex(m => m.type === type);
    if (idx !== -1) {
        map.removeLayer(markers[idx].marker);
        markers.splice(idx, 1);
        if (polyline) { map.removeLayer(polyline); polyline = null; }
        updateTimeDisplay(null);
        updateMapInstructions();
        updateNavBtnVisibility();
    }
    // FIX: quand on efface le champ, réinitialiser le flag et les champs auto
    if (type === 'depart') window._departMarkerPlaced = false;
    if (type === 'arrivee') window._arriveeMarkerPlaced = false;
    // FIX: si plus de départ ou arrivée, déverrouiller les champs distance/difficulté
    const hasD = markers.some(m => m.type === 'depart');
    const hasA = markers.some(m => m.type === 'arrivee');
    if (!hasD || !hasA) {
        unlockAutoFields();
        updateTimeDisplay(null);
        const p = document.getElementById('smart-panel');
        if (p) { p.style.display = 'none'; p.innerHTML = ''; }
    }
}

function showLocalSuggestions(query, dropdown, input, type) {
    const places = filterPlaces(query, type);
    renderLocalSuggestions(places, query, dropdown, input, type);
}

function renderLocalSuggestions(places, query, dropdown, input, type) {
    if (query && query.trim().length > 0 && places.length === 0) {
        dropdown.style.display = 'none'; dropdown.innerHTML = ''; return;
    }
    if (!query || query.trim().length === 0) {
        if (places.length === 0) { dropdown.style.display = 'none'; dropdown.innerHTML = ''; return; }
        renderSuggestionsHTML(places.slice(0, 8), '', dropdown, input, type); return;
    }
    renderSuggestionsHTML(places, query, dropdown, input, type);
}

function renderSuggestionsHTML(places, query, dropdown, input, type) {
    const typeIcons = { 'parc':'🌳','avenue':'🛣️','place':'🏛️','stade':'🏟️','corniche':'🌊','medina':'🕌','monument':'🗿','autre':'📍' };
    const typeColors = {
        'parc':     { bg:'#f0fdf4', border:'#86efac', badge:'#16a34a' },
        'avenue':   { bg:'#eff6ff', border:'#93c5fd', badge:'#2563eb' },
        'place':    { bg:'#fdf4ff', border:'#d8b4fe', badge:'#7c3aed' },
        'stade':    { bg:'#fff7ed', border:'#fdba74', badge:'#ea580c' },
        'corniche': { bg:'#f0fdfa', border:'#5eead4', badge:'#0f766e' },
        'medina':   { bg:'#fffbeb', border:'#fcd34d', badge:'#d97706' },
        'monument': { bg:'#fdf2f8', border:'#f9a8d4', badge:'#db2777' },
        'autre':    { bg:'#f8fafc', border:'#cbd5e1', badge:'#64748b' }
    };
    const parts = getRegionParts();
    const regionLabel = parts.join('-');
    const titleText = query ? `Lieux de ${regionLabel} · "${query}"` : `Lieux de ${regionLabel} — cliquez pour placer`;

    let html = `<div style="padding:10px 14px 6px;border-bottom:1px solid #f1f5f9;">
        <div style="display:flex;align-items:center;gap:7px;font-weight:800;color:#0f766e;font-size:.85rem;">📍 ${titleText}</div>
        <div style="font-size:.74rem;color:#94a3b8;margin-top:2px;">Cliquez pour placer sur la carte</div>
    </div>`;

    places.forEach(lieu => {
        const icon = typeIcons[lieu.type] || '📍';
        const c = typeColors[lieu.type] || typeColors['autre'];
        let nomDisplay = lieu.nom;
        const q = query.trim();
        if (q) {
            const idx2 = lieu.nom.toLowerCase().indexOf(q.toLowerCase());
            if (idx2 >= 0) {
                nomDisplay = lieu.nom.substring(0, idx2) +
                    `<strong>${lieu.nom.substring(idx2, idx2+q.length)}</strong>` +
                    lieu.nom.substring(idx2+q.length);
            }
        }
        html += `<div class="ai-suggest-item" data-nom="${lieu.nom}" style="padding:10px 14px;cursor:pointer;border-bottom:1px solid #f8fafc;transition:background .15s;display:flex;align-items:flex-start;gap:10px;">
            <div style="width:34px;height:34px;background:${c.bg};border:1px solid ${c.border};border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;">${icon}</div>
            <div style="flex:1;min-width:0;">
                <div style="display:flex;align-items:center;gap:7px;margin-bottom:2px;">
                    <span style="font-weight:600;font-size:.88rem;color:#102a43;">${nomDisplay}</span>
                    <span style="background:${c.badge};color:white;border-radius:5px;padding:1px 7px;font-size:.68rem;font-weight:700;text-transform:uppercase;flex-shrink:0;">${lieu.type}</span>
                </div>
                ${lieu.quartier ? `<div style="font-size:.72rem;color:#94a3b8;">📍 ${lieu.quartier}</div>` : ''}
            </div>
        </div>`;
    });

    dropdown.innerHTML = html;
    dropdown.querySelectorAll('.ai-suggest-item').forEach(item => {
        item.addEventListener('mouseenter', () => item.style.background = '#f0fdf4');
        item.addEventListener('mouseleave', () => item.style.background = '');
        item.addEventListener('click', () => {
            const nom = item.getAttribute('data-nom');
            input.value = nom;
            dropdown.style.display = 'none';
            if (type === 'depart') window._departMarkerPlaced = true;
            if (type === 'arrivee') window._arriveeMarkerPlaced = true;
            geocodeAndPlaceByName(nom, type);
        });
    });
    dropdown.style.display = 'block';
}

/* ═══════════════════════════════════════
   GÉOCODER NOM → CARTE
═══════════════════════════════════════ */
async function geocodeAndPlaceByName(name, type) {
    const feedId = type === 'depart' ? 'departFeedback' : 'arriveeFeedback';
    const el = document.getElementById(feedId);
    if (el) { el.textContent = '⏳ Localisation en cours…'; el.className = 'feedback'; }

    const parts = getRegionParts();
    const regionForGeo = type === 'arrivee' && parts.length > 1 ? parts[1] : parts[0];
    try {
        const r = await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(name+', '+regionForGeo+', Tunisie')}&format=json&limit=1&countrycodes=tn`);
        const d = await r.json();
        if (d[0]) {
            const latlng = L.latLng(parseFloat(d[0].lat), parseFloat(d[0].lon));
            await placeMarkerFromInput(latlng, name, type);
            return;
        }
    } catch(e){}
    const regionCenter = _regionCenters[regionForGeo] || cityCenter;
    await placeMarkerFromInput(L.latLng(regionCenter[0], regionCenter[1]), name, type);
}

/* ═══════════════════════════════════════
   REVERSE GEOCODE
═══════════════════════════════════════ */
async function reverseGeocode(latlng) {
    try {
        const r = await fetch(
            `https://nominatim.openstreetmap.org/reverse?lat=${latlng.lat}&lon=${latlng.lng}&format=json&accept-language=fr&zoom=18`,
            { cache: 'force-cache' }
        );
        const d = await r.json();
        const a = d.address || {};
        const specific = a.amenity || a.tourism || a.leisure || a.historic || a.shop || a.building;
        const road = a.road || a.pedestrian || a.footway || a.path;
        const area = a.neighbourhood || a.quarter || a.suburb;
        if (specific && road) return `${specific}, ${road}`;
        if (specific && area) return `${specific}, ${area}`;
        if (specific) return specific;
        if (road && area) return `${road}, ${area}`;
        if (road) return road;
        if (area) return area;
        const place = a.village || a.town || a.municipality;
        if (place) return place;
    } catch(e) {}
    return currentCity;
}

/* ═══════════════════════════════════════
   PLACER MARQUEUR (départ ou arrivée)
═══════════════════════════════════════ */
async function placeMarkerFromInput(latlng, placeName, type) {
    // Supprimer l'ancien marqueur du même type
    const idx = markers.findIndex(m => m.type === type);
    if (idx !== -1) { map.removeLayer(markers[idx].marker); markers.splice(idx, 1); }

    const targetLatLng = cityBounds.contains(latlng) ? latlng : L.latLng(cityCenter[0], cityCenter[1]);
    const cfg = type === 'depart'
        ? { color:'#16a34a', label:'D', icon:'🟢', popupLabel:'Départ' }
        : { color:'#dc2626', label:'A', icon:'🔴', popupLabel:'Arrivée' };

    const markerIcon = L.divIcon({
        className:'',
        html:`<div style="width:36px;height:36px;background:${cfg.color};border-radius:50%;border:3px solid white;box-shadow:0 3px 10px rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;color:white;font-weight:900;font-size:14px;">${cfg.label}</div>`,
        iconSize:[36,36], iconAnchor:[18,18]
    });

    const marker = L.marker(targetLatLng, { icon: markerIcon, draggable: true }).addTo(map);
    marker.bindPopup(`${cfg.icon} <strong>${cfg.popupLabel}</strong><br>${placeName}`).openPopup();

    marker.on('dragend', async () => {
        const newLatLng = marker.getLatLng();
        const newName = await reverseGeocode(newLatLng);
        const fieldId = type === 'depart' ? 'point_depart' : 'point_arrivee';
        const feedId  = type === 'depart' ? 'departFeedback' : 'arriveeFeedback';
        const el = document.getElementById(fieldId);
        if (el) el.value = newName;
        if (type === 'depart') window._departMarkerPlaced = true;
        if (type === 'arrivee') window._arriveeMarkerPlaced = true;
        triggerFeedback(feedId, newName, type);
        marker.setPopupContent(`${cfg.icon} <strong>${cfg.popupLabel}</strong><br>${newName}`);
        await recomputeAll();
    });

    // Insérer départ au début, arrivée à la fin (les waypoints sont au milieu)
    if (type === 'depart') markers.unshift({ type, latlng: targetLatLng, marker });
    else {
        // Insérer après le dernier waypoint mais avant rien d'autre
        markers.push({ type, latlng: targetLatLng, marker });
    }

    const feedId = type === 'depart' ? 'departFeedback' : 'arriveeFeedback';
    if (type === 'depart') window._departMarkerPlaced = true;
    else window._arriveeMarkerPlaced = true;
    triggerFeedback(feedId, placeName, type);

    map.setView(targetLatLng, Math.max(map.getZoom(), 14), { animate: true });
    await recomputeAll();
    updateMapInstructions();
    updateNavBtnVisibility();
}

/* ═══════════════════════════════════════
   SNAP TO NEAREST ROAD
═══════════════════════════════════════ */
const _snapCache = new Map();
async function snapToRoad(latlng) {
    const key = `${latlng.lat.toFixed(4)},${latlng.lng.toFixed(4)}`;
    if (_snapCache.has(key)) return _snapCache.get(key);
    try {
        const url = `https://router.project-osrm.org/nearest/v1/driving/${latlng.lng},${latlng.lat}?number=1`;
        const r = await fetch(url);
        const d = await r.json();
        if (d.code === 'Ok' && d.waypoints && d.waypoints[0]) {
            const loc = d.waypoints[0].location;
            const result = L.latLng(loc[1], loc[0]);
            _snapCache.set(key, result);
            return result;
        }
    } catch(e) {}
    return latlng;
}

/* ═══════════════════════════════════════
   MAP CLICK
   - 1er clic : place le départ
   - 2ème clic : place l'arrivée
   - Clics suivants : ajoute des waypoints d'ajustement
     (petits points verts glissables, ne comptent PAS comme étapes)
═══════════════════════════════════════ */
async function onMapClick(e) {
    const rawLatLng = e.latlng;
    if (!cityBounds.contains(rawLatLng)) return;

    const hasD = markers.some(m => m.type === 'depart');
    const hasA = markers.some(m => m.type === 'arrivee');

    if (!hasD) {
        const snapped = await snapToRoad(rawLatLng);
        const name = await reverseGeocode(snapped);
        const depEl = document.getElementById('point_depart');
        if (depEl) depEl.value = name;
        window._departMarkerPlaced = true;
        await placeMarkerFromInput(snapped, name, 'depart');
        return;
    }
    if (!hasA) {
        const snapped = await snapToRoad(rawLatLng);
        const name = await reverseGeocode(snapped);
        const arrEl = document.getElementById('point_arrivee');
        if (arrEl) arrEl.value = name;
        window._arriveeMarkerPlaced = true;
        await placeMarkerFromInput(snapped, name, 'arrivee');
        return;
    }
    // Départ + Arrivée déjà placés → ajouter un waypoint d'ajustement seulement
    const snapped = await snapToRoad(rawLatLng);
    await addWaypointBeforeArrivee(snapped);
    await recomputeAll();
    updateMapInstructions();
}

/* Ajouter un point d'ajustement (waypoint) entre les marqueurs existants */
async function addWaypointBeforeArrivee(latlng) {
    const wptCount = markers.filter(m => m.type === 'waypoint').length + 1;
    const icon = L.divIcon({
        className:'',
        html:`<div style="width:22px;height:22px;background:#0f766e;border-radius:50%;border:3px solid white;box-shadow:0 2px 8px rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;color:white;font-weight:900;font-size:9px;">${wptCount}</div>`,
        iconSize:[22,22], iconAnchor:[11,11]
    });
    const marker = L.marker(latlng, { icon, draggable: true }).addTo(map);
    marker.bindPopup(`📍 Point intermédiaire ${wptCount}`);
    marker.on('dragend', async () => {
        const newLL = marker.getLatLng();
        const snappedLL = await snapToRoad(newLL);
        marker.setLatLng(snappedLL);
        // Mettre à jour latlng dans le tableau
        const entry = markers.find(m => m.marker === marker);
        if (entry) entry.latlng = snappedLL;
        await recomputeAll();
    });
    // Insérer juste avant l'arrivée
    const arrIdx = markers.findIndex(m => m.type === 'arrivee');
    const newM = { type:'waypoint', latlng, marker };
    if (arrIdx !== -1) markers.splice(arrIdx, 0, newM);
    else markers.push(newM);
}

/* ═══════════════════════════════════════
   AUTO-PLACE points existants (mode modification)
═══════════════════════════════════════ */
async function autoPlaceExistingPoints() {
    const depEl = document.getElementById('point_depart');
    const arrEl = document.getElementById('point_arrivee');
    if (!depEl || !arrEl || (!depEl.value.trim() && !arrEl.value.trim())) return;

    async function geocodePoint(name, type) {
        if (!name) return null;
        const parts = getRegionParts();
        const regionForGeo = type === 'arrivee' && parts.length > 1 ? parts[1] : parts[0];
        try {
            const r = await fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(name+', '+regionForGeo+', Tunisie')}&format=json&limit=1&countrycodes=tn`);
            const d = await r.json();
            if (d[0]) return L.latLng(parseFloat(d[0].lat), parseFloat(d[0].lon));
        } catch(e){}
        return null;
    }

    const [dL, aL] = await Promise.all([
        geocodePoint(depEl.value.trim(), 'depart'),
        geocodePoint(arrEl.value.trim(), 'arrivee')
    ]);
    if (dL) await placeMarkerFromInput(dL, depEl.value.trim(), 'depart');
    if (aL) await placeMarkerFromInput(aL, arrEl.value.trim(), 'arrivee');
}

/* ═══════════════════════════════════════
   RECOMPUTE ALL
   FIX: distance et difficulté ne se calculent QUE si
        départ ET arrivée sont tous les deux placés et valides
═══════════════════════════════════════ */
async function recomputeAll() {
    const hasD = markers.some(m => m.type === 'depart');
    const hasA = markers.some(m => m.type === 'arrivee');

    // FIX: Ne calculer distance/difficulté que si les deux sont valides
    if (!hasD || !hasA) {
        updateTimeDisplay(null);
        unlockAutoFields();
        await updatePolyline();
        return;
    }

    await updatePolyline(); // dessine le tracé en fond
    const pts = markers.map(m => m.marker.getLatLng());
    let totalDist = 0;
    for (let i = 1; i < pts.length; i++) totalDist += pts[i-1].distanceTo(pts[i]);
    const distKm = totalDist / 1000;
    const diff = autoDifficulte(distKm);

    updateTimeDisplay(distKm);

    const distEl = document.getElementById('distance');
    if (distEl) {
        distEl.value = distKm.toFixed(2);
        distEl.readOnly = true;
        distEl.style.background = '#f0fdf4';
        distEl.style.color = '#0f766e';
        distEl.style.borderColor = '#86efac';
        distEl.style.cursor = '';
        triggerFeedback('distanceFeedback', 'Calculée automatiquement', null);
    }

    const diffEl = document.getElementById('difficulte');
    if (diffEl) {
        diffEl.value = diff;
        diffEl.disabled = true;
        diffEl.className = diffEl.className.replace(/\bdiff-\S+/g,'').trim();
        if (diff === 'facile') diffEl.classList.add('diff-facile');
        else if (diff === 'moyen') diffEl.classList.add('diff-moyen');
        else if (diff === 'difficile') diffEl.classList.add('diff-difficile');
        triggerFeedback('difficulteFeedback', 'Détectée automatiquement', null);
        let hidden = document.getElementById('difficulte_hidden');
        if (!hidden) {
            hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.id = 'difficulte_hidden';
            hidden.name = 'difficulte';
            diffEl.parentNode.appendChild(hidden);
            diffEl.name = '';
        }
        hidden.value = diff;
    }

    const depM = markers.find(m => m.type === 'depart');
    const arrM = markers.find(m => m.type === 'arrivee');
    if (depM && arrM) analyzeZonesAI(depM.marker.getLatLng(), arrM.marker.getLatLng(), currentCity, distKm);
}

/* ═══════════════════════════════════════
   OSRM ROUTE GEOMETRY
═══════════════════════════════════════ */
let _osrmRouteSteps = [];
let _osrmRouteGeometry = [];

async function getOSRMFullRoute(waypoints) {
    const coords = waypoints.map(ll => `${ll.lng},${ll.lat}`).join(';');
    try {
        const url = `${OSRM_API}/${coords}?overview=full&geometries=geojson&steps=true`;
        const r = await fetch(url);
        const d = await r.json();
        if (d.routes && d.routes[0]) return d.routes[0];
    } catch(e) {}
    return null;
}

async function updatePolyline() {
    if (polyline) { map.removeLayer(polyline); polyline = null; }
    const pts = markers.map(m => m.marker.getLatLng());
    if (pts.length < 2) { updateNavBtnVisibility(); return; }

    const route = await getOSRMFullRoute(pts);
    if (route && route.geometry && route.geometry.coordinates) {
        const latLngs = route.geometry.coordinates.map(c => L.latLng(c[1], c[0]));
        _osrmRouteGeometry = latLngs;
        _osrmRouteSteps = [];
        (route.legs || []).forEach(leg => {
            (leg.steps || []).forEach(step => { _osrmRouteSteps.push(step); });
        });
        polyline = L.polyline(latLngs, { color:'#1d4ed8', weight:6, opacity:.92, lineJoin:'round' }).addTo(map);
    } else {
        _osrmRouteGeometry = pts;
        _osrmRouteSteps = [];
        polyline = L.polyline(pts, { color:'#1d4ed8', weight:5, opacity:.85, dashArray:'10,5', lineJoin:'round' }).addTo(map);
    }
    // FIX: fitBounds seulement si navigation pas active
    if (polyline && !_navActive) map.fitBounds(polyline.getBounds().pad(0.18));
    updateNavBtnVisibility();
}

function autoDifficulte(d) { return d <= 10 ? 'facile' : d <= 21 ? 'moyen' : 'difficile'; }

/* Distance saisie manuelle → auto-difficulté */
(function() {
    function _applyAutoDiff() {
        const distEl = document.getElementById('distance');
        const diffEl = document.getElementById('difficulte');
        if (!distEl || !diffEl) return;
        const val = parseFloat(distEl.value);
        if (isNaN(val) || val <= 0) return;
        if (distEl.readOnly) return; // déjà géré par recomputeAll
        const diff = autoDifficulte(val);
        diffEl.value = diff;
        diffEl.className = diffEl.className.replace(/\bdiff-\S+/g, '').trim();
        diffEl.classList.add('diff-' + diff);
        const hid = document.getElementById('difficulte_hidden');
        if (hid) hid.value = diff;
        const fb = document.getElementById('difficulteFeedback');
        if (fb) { fb.textContent = 'Détectée automatiquement'; fb.className = 'feedback success'; }
    }
    function _bindDistanceListener() {
        const distEl = document.getElementById('distance');
        if (!distEl || distEl._autoDiffBound) return;
        distEl._autoDiffBound = true;
        distEl.addEventListener('input', _applyAutoDiff);
        distEl.addEventListener('change', _applyAutoDiff);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', _bindDistanceListener);
    } else {
        _bindDistanceListener();
    }
})();

/* ═══════════════════════════════════════
   IA — callAI / getOSRMDistance / analyzeZonesAI
═══════════════════════════════════════ */
async function callAI(prompt, maxTokens = 800) {
    const resp = await fetch(AI_PROXY, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            model: 'claude-haiku-4-5-20251001',
            max_tokens: maxTokens,
            messages: [{ role: 'user', content: prompt }]
        })
    });
    if (!resp.ok) throw new Error('Proxy error ' + resp.status);
    const data = await resp.json();
    const text = data.content?.find(b => b.type === 'text')?.text || '';
    return text.replace(/```json|```/g, '').trim();
}

async function getOSRMDistance(depLatLng, arrLatLng) {
    try {
        const url = `${OSRM_API}/${depLatLng.lng},${depLatLng.lat};${arrLatLng.lng},${arrLatLng.lat}?overview=false`;
        const r = await fetch(url);
        const d = await r.json();
        if (d.routes && d.routes[0]) return d.routes[0].distance / 1000;
    } catch(e) {}
    return null;
}

async function analyzeZonesAI(depLL, arrLL, city, distKm) {
    const panel = document.getElementById('smart-panel');
    if (!panel) return;
    panel.style.display = 'none'; panel.innerHTML = '';

    const osrmDist = await getOSRMDistance(depLL, arrLL);
    const finalDist = osrmDist || distKm;

    if (osrmDist) {
        const distEl = document.getElementById('distance');
        if (distEl) {
            distEl.value = osrmDist.toFixed(2);
            triggerFeedback('distanceFeedback', 'Calculée via OSRM (' + osrmDist.toFixed(1) + ' km)', null);
        }
        const diff = autoDifficulte(osrmDist);
        const diffEl = document.getElementById('difficulte');
        if (diffEl) {
            diffEl.value = diff;
            diffEl.className = diffEl.className.replace(/\bdiff-\S+/g,'').trim();
            if (diff === 'facile') diffEl.classList.add('diff-facile');
            else if (diff === 'moyen') diffEl.classList.add('diff-moyen');
            else if (diff === 'difficile') diffEl.classList.add('diff-difficile');
            let hidden = document.getElementById('difficulte_hidden');
            if (hidden) hidden.value = diff;
        }
        updateTimeDisplay(osrmDist);
    }

    const [depName, arrName] = await Promise.all([reverseGeocode(depLL), reverseGeocode(arrLL)]);
    const prompt = `Tu es expert en géographie tunisienne et organisation de marathons.
Parcours dans "${city}", départ:"${depName}"(${depLL.lat.toFixed(4)},${depLL.lng.toFixed(4)}), arrivée:"${arrName}"(${arrLL.lat.toFixed(4)},${arrLL.lng.toFixed(4)}), distance:${finalDist.toFixed(2)}km.
Identifie les zones géographiques RÉELLES traversées par ce parcours en Tunisie.
Réponds UNIQUEMENT en JSON valide :
{"zones":[{"nom":"...","type":"touristique|résidentiel|commercial|historique|sportif|parc","desc":"..."}],"recommandation":"...conseil utile pour les coureurs en 1 phrase..."}
Maximum 4 zones. Utilise uniquement des lieux géographiques réels tunisiens.`;

    try {
        const text = await callAI(prompt, 700);
        renderAIZones(JSON.parse(text));
    } catch(e) {
        panel.innerHTML = ''; panel.style.display = 'none';
    }
}

function renderAIZones(data) {
    const panel = document.getElementById('smart-panel');
    if (!panel || !data) return;
    const tc = {
        'touristique':{bg:'#fffbeb',bd:'#fde68a',badge:'#d97706'},
        'historique': {bg:'#fdf4ff',bd:'#e9d5ff',badge:'#7c3aed'},
        'commercial': {bg:'#eff6ff',bd:'#bfdbfe',badge:'#2563eb'},
        'résidentiel':{bg:'#f0fdf4',bd:'#bbf7d0',badge:'#16a34a'},
        'sportif':    {bg:'#f0fdfa',bd:'#99f6e4',badge:'#0f766e'},
        'parc':       {bg:'#f7fee7',bd:'#bef264',badge:'#65a30d'}
    };
    const zonesHTML = (data.zones||[]).map(z => {
        const c = tc[z.type]||tc['commercial'];
        return `<div style="background:${c.bg};border:1px solid ${c.bd};border-radius:10px;padding:10px 12px;margin-bottom:8px;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                <span style="background:${c.badge};color:white;border-radius:6px;padding:2px 8px;font-size:.72rem;font-weight:700;text-transform:uppercase;">${z.type}</span>
                <span style="font-weight:700;font-size:.9rem;color:#102a43;">${z.nom}</span>
            </div><div style="font-size:.83rem;color:#475569;line-height:1.5;">${z.desc}</div>
        </div>`;
    }).join('');
    panel.innerHTML = `<div style="background:#f0fdf9;border:1px solid #a7f3d0;border-radius:14px;padding:14px;">
        <div style="font-weight:800;color:#065f46;font-size:.9rem;margin-bottom:10px;">📍 Zones traversées — Recommandations IA</div>
        ${zonesHTML}
        ${data.recommandation?`<div style="background:white;border-radius:10px;padding:10px;border:1px solid #d1fae5;font-size:.85rem;color:#065f46;font-style:italic;">💡 ${data.recommandation}</div>`:''}
    </div>`;
    panel.style.display = 'block';
}

/* ═══════════════════════════════════════
   MODAL TRAJETS RECOMMANDÉS
═══════════════════════════════════════ */
function ensureRoutesModal() {
    if (document.getElementById('routesModal')) return;
    const style = document.createElement('style');
    style.textContent = `
    #routesModal { display:none;position:fixed;inset:0;z-index:9999;background:rgba(16,42,67,.6);backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:16px; }
    #routesModal.open { display:flex; }
    #routesModalBox { background:#fff;border-radius:24px;width:min(680px,100%);max-height:88vh;display:flex;flex-direction:column;box-shadow:0 32px 80px rgba(16,42,67,.24);overflow:hidden;animation:rmSlide .25s cubic-bezier(.22,1,.36,1); }
    @keyframes rmSlide { from{transform:translateY(28px);opacity:0} to{transform:none;opacity:1} }
    #routesModalHeader { display:flex;align-items:center;justify-content:space-between;padding:18px 22px 14px;border-bottom:1px solid #e6edf3;flex-shrink:0; }
    #routesModalHeader .rm-title { font-size:1.05rem;font-weight:800;color:#102a43;display:flex;align-items:center;gap:8px; }
    #routesModalClose { background:none;border:none;cursor:pointer;font-size:1.5rem;color:#94a3b8;line-height:1;padding:4px 8px;border-radius:8px; }
    #routesModalClose:hover { background:#f1f5f9;color:#475569; }
    #routesModalBody { overflow-y:auto;padding:14px 18px;flex:1; }
    .rm-section-title { font-size:.82rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;padding:10px 0 6px;display:flex;align-items:center;gap:6px; }
    .rm-route-card { border-radius:14px;padding:14px 16px;margin-bottom:10px;border:1.5px solid;cursor:pointer;transition:transform .15s,box-shadow .15s; }
    .rm-route-card:hover { transform:translateY(-2px);box-shadow:0 8px 22px rgba(16,42,67,.12); }
    .rm-route-card .rm-route-head { display:flex;align-items:center;justify-content:space-between;margin-bottom:6px; }
    .rm-route-card .rm-route-label { font-weight:700;font-size:.95rem; }
    .rm-route-card .rm-route-dist { font-size:.88rem;font-weight:700;opacity:.8; }
    .rm-route-card .rm-route-pts { font-size:.83rem;color:#64748b;line-height:1.5; }
    .rm-route-card .rm-route-btn { margin-top:10px;background:linear-gradient(135deg,#0f766e,#14b8a6);color:#fff;border:none;border-radius:10px;padding:8px 18px;font-weight:700;font-size:.85rem;cursor:pointer;float:right; }
    `;
    document.head.appendChild(style);
    const modal = document.createElement('div');
    modal.id = 'routesModal';
    modal.innerHTML = `
    <div id="routesModalBox">
        <div id="routesModalHeader">
            <span class="rm-title">🧠 Trajets recommandés — ${currentCity}</span>
            <button id="routesModalClose" type="button">✕</button>
        </div>
        <div id="routesModalBody"><div style="padding:24px;text-align:center;color:#64748b;">Génération en cours…</div></div>
    </div>`;
    document.body.appendChild(modal);
    document.getElementById('routesModalClose').addEventListener('click', closeRoutesModal);
    modal.addEventListener('click', e => { if (e.target === modal) closeRoutesModal(); });
}

function closeRoutesModal() {
    const m = document.getElementById('routesModal');
    if (m) m.classList.remove('open');
}

function openRoutesModal() {
    ensureRoutesModal();
    const title = document.querySelector('#routesModalHeader .rm-title');
    if (title) title.innerHTML = `🧠 Trajets recommandés — ${currentCity}`;
    document.getElementById('routesModal').classList.add('open');
}

async function recommendRoutes() {
    openRoutesModal();
    const body = document.getElementById('routesModalBody');
    body.innerHTML = `<div style="display:flex;align-items:center;gap:12px;padding:28px 20px;color:#6366f1;font-weight:600;">
        <div style="width:22px;height:22px;border:3px solid #e0e7ff;border-top-color:#6366f1;border-radius:50%;animation:spin .8s linear infinite;flex-shrink:0;"></div>
        <span>L'IA génère 6 trajets optimaux…</span>
    </div><style>@keyframes spin{to{transform:rotate(360deg)}}</style>`;

    const parts = getRegionParts();
    const isMulti = parts.length > 1;
    const regionA = parts[0];
    const regionB = isMulti ? parts[1] : parts[0];

    const prompt = `Tu es expert en organisation de marathons en Tunisie.
Marathon dans : "${currentCity}".
${isMulti
    ? `Type MULTI-RÉGION : départ OBLIGATOIREMENT dans "${regionA}", arrivée OBLIGATOIREMENT dans "${regionB}".`
    : `Type MONO-RÉGION : départ ET arrivée dans "${regionA}".`}
Génère exactement 6 trajets avec des lieux RÉELS et CONNUS de ces gouvernorats tunisiens :
- 2 trajets "facile" (environ 5 à 15 km)
- 2 trajets "moyen" (environ 16 à 28 km)
- 2 trajets "difficile" (environ 29 à 42 km)
Réponds UNIQUEMENT en JSON valide :
{"routes":[{"id":1,"difficulty":"facile","depart":"Lieu réel","arrivee":"Lieu réel","description":"courte description"},{"id":2,"difficulty":"facile","depart":"...","arrivee":"...","description":"..."},{"id":3,"difficulty":"moyen","depart":"...","arrivee":"...","description":"..."},{"id":4,"difficulty":"moyen","depart":"...","arrivee":"...","description":"..."},{"id":5,"difficulty":"difficile","depart":"...","arrivee":"...","description":"..."},{"id":6,"difficulty":"difficile","depart":"...","arrivee":"...","description":"..."}]}`;

    try {
        const text = await callAI(prompt, 1200);
        const parsed = JSON.parse(text);
        const routes = parsed.routes || [];
        body.innerHTML = `<div style="display:flex;align-items:center;gap:10px;padding:12px 18px;color:#6366f1;font-size:.88rem;font-weight:600;">
            <div style="width:16px;height:16px;border:2px solid #e0e7ff;border-top-color:#6366f1;border-radius:50%;animation:spin .8s linear infinite;flex-shrink:0;"></div>
            Calcul des vraies distances via OSRM…
        </div>`;
        const routesWithDist = await Promise.all(routes.map(async route => {
            try {
                const [gDep, gArr] = await Promise.all([
                    fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(route.depart+', '+regionA+', Tunisie')}&format=json&limit=1&countrycodes=tn`).then(r=>r.json()),
                    fetch(`https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(route.arrivee+', '+(isMulti?regionB:regionA)+', Tunisie')}&format=json&limit=1&countrycodes=tn`).then(r=>r.json())
                ]);
                if (gDep[0] && gArr[0]) {
                    const depLL = L.latLng(parseFloat(gDep[0].lat), parseFloat(gDep[0].lon));
                    const arrLL = L.latLng(parseFloat(gArr[0].lat), parseFloat(gArr[0].lon));
                    const dist = await getOSRMDistance(depLL, arrLL);
                    return { ...route, distance_km: dist ? parseFloat(dist.toFixed(1)) : '—' };
                }
            } catch(e) {}
            return { ...route, distance_km: '—' };
        }));
        renderRoutesModal(routesWithDist);
    } catch(e) {
        body.innerHTML = `<div style="padding:16px;background:#fef2f2;border-radius:12px;margin:8px;">
            <div style="color:#b91c1c;font-weight:700;margin-bottom:6px;">❌ Erreur de génération IA</div>
            <div style="font-size:.85rem;color:#64748b;">Configurez votre clé API dans <code>ai_proxy.php</code>.</div>
        </div>`;
    }
}

function renderRoutesModal(routes) {
    const body = document.getElementById('routesModalBody');
    if (!routes || routes.length === 0) {
        body.innerHTML = `<div style="padding:20px;color:#b91c1c;">Aucun trajet généré. Réessayez.</div>`; return;
    }
    const groups = { facile: [], moyen: [], difficile: [] };
    routes.forEach(r => { if (groups[r.difficulty]) groups[r.difficulty].push(r); });
    const cfg = {
        facile:    { label:'🟢 Facile',    bg:'#f0fdf4', border:'#86efac', color:'#065f46', badge:'#16a34a' },
        moyen:     { label:'🟡 Moyen',     bg:'#fefce8', border:'#fde047', color:'#a16207', badge:'#ca8a04' },
        difficile: { label:'🔴 Difficile', bg:'#fef2f2', border:'#fca5a5', color:'#b91c1c', badge:'#dc2626' }
    };
    let html = '';
    ['facile','moyen','difficile'].forEach(diff => {
        const c = cfg[diff]; const list = groups[diff];
        if (!list || list.length === 0) return;
        html += `<div class="rm-section-title" style="color:${c.badge};">${c.label}</div>`;
        list.forEach(route => {
            const routeData = JSON.stringify({ depart: route.depart, arrivee: route.arrivee, difficulty: route.difficulty }).replace(/"/g,'&quot;');
            const distLabel = route.distance_km !== '—' ? `📏 ${route.distance_km} km` : '📏 Distance à calculer';
            html += `<div class="rm-route-card" style="background:${c.bg};border-color:${c.border};" onclick="applyRoute(${routeData})">
                <div class="rm-route-head">
                    <span class="rm-route-label" style="color:${c.color};">${route.depart} → ${route.arrivee}</span>
                    <span class="rm-route-dist" style="color:${c.badge};">${distLabel}</span>
                </div>
                <div class="rm-route-pts">${route.description || ''}</div>
                <div style="text-align:right;margin-top:8px;"><button class="rm-route-btn" type="button">Choisir ce trajet →</button></div>
            </div>`;
        });
    });
    body.innerHTML = html;
}

async function applyRoute(route) {
    closeRoutesModal();
    const depEl = document.getElementById('point_depart');
    const arrEl = document.getElementById('point_arrivee');
    if (depEl) depEl.value = route.depart;
    if (arrEl) arrEl.value = route.arrivee;
    await geocodeAndPlaceByName(route.depart, 'depart');
    await geocodeAndPlaceByName(route.arrivee, 'arrivee');
}

/* ═══════════════════════════════════════
   UNDO & RESET
   FIX: undoLastPoint supprime le DERNIER point
        (départ, arrivée, OU waypoint)
        dans l'ordre inverse d'insertion
   FIX: reset efface aussi le tracé et stoppe la navigation
═══════════════════════════════════════ */
async function undoLastPoint() {
    if (markers.length === 0) return;
    // FIX: On supprime le dernier marqueur dans le tableau (LIFO)
    // L'arrivée est toujours à la fin, les waypoints avant elle, le départ au début
    const last = markers[markers.length - 1];
    markers.pop();
    map.removeLayer(last.marker);

    if (polyline) { map.removeLayer(polyline); polyline = null; }
    _osrmRouteSteps = [];
    _osrmRouteGeometry = [];

    if (last.type === 'depart') {
        const el = document.getElementById('point_depart'); if (el) el.value = '';
        const fb = document.getElementById('departFeedback'); if (fb) { fb.textContent=''; fb.className='feedback'; }
        window._departMarkerPlaced = false;
    }
    if (last.type === 'arrivee') {
        const el = document.getElementById('point_arrivee'); if (el) el.value = '';
        const fb = document.getElementById('arriveeFeedback'); if (fb) { fb.textContent=''; fb.className='feedback'; }
        window._arriveeMarkerPlaced = false;
    }
    // waypoint: pas de champ à effacer

    const hasD = markers.some(m => m.type === 'depart');
    const hasA = markers.some(m => m.type === 'arrivee');
    if (hasD && hasA) {
        await recomputeAll();
    } else {
        unlockAutoFields();
        updateTimeDisplay(null);
        const p = document.getElementById('smart-panel');
        if (p) { p.style.display = 'none'; p.innerHTML = ''; }
    }
    updateMapInstructions();
    updateNavBtnVisibility();
}

function resetMap() {
    // FIX: stopper navigation si active avant tout
    if (_navActive) stopNavigation();

    // Supprimer tous les marqueurs
    markers.forEach(m => map.removeLayer(m.marker));
    markers = [];

    // Supprimer le tracé
    if (polyline) { map.removeLayer(polyline); polyline = null; }

    // Supprimer marqueur de navigation si présent
    if (_navMarker) { map.removeLayer(_navMarker); _navMarker = null; }

    // FIX: supprimer le panneau de navigation s'il est affiché
    const navPanel = document.getElementById('nav-panel');
    if (navPanel) navPanel.remove();

    // Réinitialiser état OSRM
    _osrmRouteSteps = [];
    _osrmRouteGeometry = [];

    // Réinitialiser flags
    window._departMarkerPlaced = false;
    window._arriveeMarkerPlaced = false;

    // Vider les champs
    ['point_depart','point_arrivee','distance'].forEach(id => {
        const el = document.getElementById(id); if (el) el.value = '';
    });
    const diffEl = document.getElementById('difficulte');
    if (diffEl) { diffEl.value = ''; diffEl.name = 'difficulte'; diffEl.className = diffEl.className.replace(/\bdiff-\S+/g,'').trim(); }
    const hiddenDiff = document.getElementById('difficulte_hidden');
    if (hiddenDiff) hiddenDiff.parentNode.removeChild(hiddenDiff);

    // Vider les feedbacks
    ['departFeedback','arriveeFeedback','distanceFeedback','difficulteFeedback'].forEach(id => {
        const el = document.getElementById(id); if (el) { el.textContent=''; el.className='feedback'; }
    });

    unlockAutoFields();
    _aiSuggestionsCache = { depart: null, arrivee: null };
    _iaRoutesCache = null;

    const p = document.getElementById('smart-panel');
    if (p) { p.style.display = 'none'; p.innerHTML = ''; }

    updateTimeDisplay(null);
    updateMapInstructions();
    updateNavBtnVisibility();

    // FIX: réafficher le bouton démarrer navigation (il était caché)
    const btnNav = document.getElementById('btn-start-nav');
    if (btnNav) btnNav.style.display = 'none'; // caché car plus de tracé

    map.setView(cityCenter, cityZoom);
}

function unlockAutoFields() {
    const distEl = document.getElementById('distance');
    if (distEl) { distEl.readOnly = false; distEl.style.background=''; distEl.style.color=''; distEl.style.cursor=''; distEl.style.borderColor=''; }
    const diffEl = document.getElementById('difficulte');
    if (diffEl) { diffEl.disabled = false; diffEl.name = 'difficulte'; diffEl.style.background=''; diffEl.style.color=''; diffEl.style.cursor=''; diffEl.style.borderColor=''; }
    const hiddenDiff = document.getElementById('difficulte_hidden');
    if (hiddenDiff) hiddenDiff.parentNode.removeChild(hiddenDiff);
}

/* ═══════════════════════════════════════
   MAP INSTRUCTIONS
═══════════════════════════════════════ */
function updateMapInstructions() {
    const el = document.getElementById('map-instructions'); if (!el) return;
    const hasD = markers.some(m => m.type === 'depart');
    const hasA = markers.some(m => m.type === 'arrivee');
    const parts = getRegionParts();
    const isMulti = parts.length > 1;

    if (!hasD && !hasA) {
        const hint = isMulti
            ? `💡 Cliquez dans <strong>Point de Départ</strong> pour les lieux de <strong>${parts[0]}</strong> · Point d'Arrivée pour <strong>${parts[1]}</strong>`
            : `💡 Cliquez dans un champ Départ ou Arrivée pour voir les suggestions · ou cliquez directement sur la carte`;
        el.innerHTML = `<span style="color:#0f766e;font-weight:700;">${hint}</span>`;
    } else if (hasD && !hasA) {
        const hint = isMulti
            ? `🏁 Maintenant : choisissez le <u>Point d'Arrivée</u> dans <strong>${parts[1]}</strong>`
            : `🏁 Maintenant : choisissez le <u>Point d'Arrivée</u> ou cliquez sur la carte`;
        el.innerHTML = `<span style="color:#dc2626;font-weight:700;">${hint}</span>`;
    } else {
        el.innerHTML = `<span style="color:#475569;">✅ Tracé complet · <em>Cliquez sur la carte pour ajuster le tracé (glissez les petits points verts)</em></span>`;
    }
}

/* ═══════════════════════════════════════
   TOAST
═══════════════════════════════════════ */
function showMapToast(msg, type) {
    let t = document.getElementById('map-toast');
    if (!t) {
        t = document.createElement('div');
        t.id = 'map-toast';
        t.style.cssText = 'position:fixed;bottom:20px;left:50%;transform:translateX(-50%);padding:10px 20px;border-radius:12px;font-weight:700;font-size:.9rem;z-index:9999;transition:opacity .3s;';
        document.body.appendChild(t);
    }
    const col = { 'warn':['#fef3c7','#92400e','#fde68a'], 'info':['#eff6ff','#1d4ed8','#bfdbfe'], 'ok':['#dcfce7','#065f46','#86efac'] };
    const [bg, color, border] = (col[type] || col['info']);
    t.style.background = bg; t.style.color = color; t.style.border = `1px solid ${border}`;
    t.textContent = msg; t.style.opacity = '1';
    clearTimeout(t._timer); t._timer = setTimeout(() => { t.style.opacity = '0'; }, 3000);
}

/* ═══════════════════════════════════════
   FEEDBACK — VALIDATION
═══════════════════════════════════════ */
function triggerFeedback(id, val, type) {
    const el = document.getElementById(id); if (!el) return;
    if (!val) { el.textContent=''; el.className='feedback'; return; }

    if (id === 'distanceFeedback' || id === 'difficulteFeedback') {
        el.textContent = '✅ ' + val; el.className = 'feedback success'; return;
    }

    const isDepart = id === 'departFeedback';

    // Si placé via carte/suggestion → toujours valide
    if (isDepart && window._departMarkerPlaced) {
        el.textContent = '✅ ' + val; el.className = 'feedback success'; return;
    }
    if (!isDepart && window._arriveeMarkerPlaced) {
        el.textContent = '✅ ' + val; el.className = 'feedback success'; return;
    }

    // Saisie manuelle : refuser si c'est le nom d'un gouvernorat entier
    const valLow = val.trim().toLowerCase();
    const isRegionName = getRegionParts().some(r => r.toLowerCase() === valLow) ||
        Object.keys(CITY_PLACES).some(r => r.toLowerCase() === valLow);
    if (isRegionName) {
        el.textContent = '❌ Saisissez un lieu précis, pas le nom du gouvernorat.';
        el.className = 'feedback error'; return;
    }

    const validPlaces = isDepart ? getPlacesForDepart() : getPlacesForArrivee();
    if (validPlaces.length > 0) {
        const v = val.trim().toLowerCase();
        const found = validPlaces.some(p =>
            p.nom.toLowerCase() === v || p.nom.toLowerCase().includes(v) || v.includes(p.nom.toLowerCase())
        );
        if (!found) {
            el.textContent = `❌ Ce lieu n'existe pas dans ${getRegionParts().join('-')}`;
            el.className = 'feedback error'; return;
        }
    }
    el.textContent = '✅ ' + val; el.className = 'feedback success';
}

/* Navigation supprimée — stubs vides pour compatibilité */
var _navActive = false;
var _navMarker = null;

function updateNavBtnVisibility() {
    var btn = document.getElementById('btn-start-nav');
    if (btn) btn.remove();
}
function injectNavButton() {}
function startNavigation() {}
function stopNavigation() {
    _navActive = false;
    if (_navMarker && map) { map.removeLayer(_navMarker); _navMarker = null; }
    var p = document.getElementById('nav-panel');
    if (p) p.remove();
}
function navGoToStep() {}
function renderNavPanel() {}

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        var btn = document.getElementById('btn-start-nav');
        if (btn) btn.remove();
    }, 300);
});
