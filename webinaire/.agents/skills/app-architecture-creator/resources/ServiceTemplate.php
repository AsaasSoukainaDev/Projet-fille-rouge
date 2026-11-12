<?php
    require_once "../dao/{{Entity}}DAO.php";
    require_once "BaseService.php";

    class {{Entity}}Service extends BaseService {
        private {{Entity}}DAO ${{entityCamel}}Dao;

        public function __construct() {
            $this->{{entityCamel}}Dao = new {{Entity}}DAO();
        }

        public function lister{{Entities}}(): array {
            $items = $this->{{entityCamel}}Dao->AfficherTous();
            $data = [];
            foreach ($items as $item) {
                $data[] = [
                    "id" => $item->getId(),
{{serviceArrayMappings}}
                ];
            }
            return $data;
        }

        public function ajouter{{Entity}}(): array {
            $input = json_decode(file_get_contents("php://input"), true);
            try {
                http_response_code(200);
                $this->{{entityCamel}}Dao->ajouter(
{{serviceInputParams}}
                );
                return [
                    "success" => true,
                    "message" => "{{Entity}} est bien ajouté(e)"
                ];
            } catch (Error $e) {
                http_response_code(500);
                return [
                    "success" => false,
                    "message" => $e->getMessage()
                ];
            }
        }
    }
?>

