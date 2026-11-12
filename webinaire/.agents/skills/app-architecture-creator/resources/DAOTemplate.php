<?php
    require_once "../classes/{{Entity}}.php";

    class {{Entity}}DAO {
        private static string $dataFile = "../data/{{Entities}}.json";

        public function AfficherTous(): array {
            $data = [];
            if (!file_exists(self::$dataFile)) {
                file_put_contents(self::$dataFile, json_encode([], JSON_PRETTY_PRINT));
                return [];
            }

            $items = json_decode(file_get_contents(self::$dataFile), true) ?? [];
            foreach ($items as $item) {
                $obj = new {{Entity}}(
                    $item["id"]{{itemArgs}}
                );
                $data[] = $obj;
            }
            return $data;
        }

        public function ajouter({{addParams}}): void {
            $data = [];
            $items = $this->AfficherTous();
            $id = count($items) + 1;

            if ({{validationCondition}}) {
                throw new Error("Tous les champs sont obligatoires");
            }

            $items[] = new {{Entity}}($id{{addEntityArgs}});
            foreach ($items as $item) {
                $data[] = [
                    "id" => $item->getId(),
{{arrayMappings}}
                ];
            }

            file_put_contents(self::$dataFile, json_encode($data, JSON_PRETTY_PRINT));
        }
    }
?>

