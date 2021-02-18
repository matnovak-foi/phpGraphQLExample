<?php
// php -S localhost:8080 ./graphql.php &
// curl http://localhost:8080 -d '{"query": "{Upit : korisnici {ime,prezime}}" }'
// curl http://localhost:8080 -d '{"query": "{Upit : korisnici(ime: \"M\") {ime,prezime}}" }'
// curl http://localhost:8080 -d '{"query": "mutation {   dodajKorisnika(ime: \"Pero\", prezime: \"perić\", telefon: 888888) {ime, prezime}}" }'
require_once __DIR__ . '/../vendor/autoload.php';
use GraphQL\Utils\BuildSchema;
use GraphQL\GraphQL;

$podaci = [
	 'korisnici' => function($rootValue, $args, $context) {
		 $korisnici=array();
		 $d=fopen("podaci.csv","r");
		 while($red = fgetcsv($d)){
			 $korisnik["ime"] = $red[0];
			 $korisnik["prezime"] = $red[1];
			 $korisnik["telefon"] = $red[2];
				if(isset($args["ime"])){
					if(strpos($korisnik["ime"],$args["ime"])!==false)
						$korisnici[] = $korisnik;
				} else { $korisnici[] = $korisnik; }
		 }
		 fclose($d);
		 return $korisnici;
	 },
	 'dodajKorisnika' => function($rootValue, $args, $context) {
		 $korisnik = $args["ime"].",".$args["prezime"].",".$args["telefon"]."\n";
		 $d=fopen("podaci.csv","a+");
		 fwrite($d,$korisnik);
		 fclose($d);
		 return ['ime' => $args["ime"],
			 'prezime' => $args['prezime'],
				'telefon'=> $args['telefon']];
	 }
];


$shemaSpecifikacija = file_get_contents('shema.graphql');
$shema = BuildSchema::build($shemaSpecifikacija);

try {
    $zahtjev = file_get_contents('php://input');
    $zahtjevJSON = json_decode($zahtjev, true);
    $upit = $zahtjevJSON['query'];
    $rezultat = GraphQL::executeQuery($shema, $upit, $podaci);
    $odgovor = $rezultat->toArray();
} catch (\Exception $e) {
    $odgovor = [
        'greska' => [
            'poruka' => $e->getMessage()
        ]
    ];
}
header('Content-Type: application/json; charset=UTF-8');
echo json_encode($odgovor);
?>
