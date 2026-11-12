<?php
    class {{Entity}} {
        private int $id;
{{properties}}

        public function __construct(int $id{{constructorParams}}) {
            $this->id = $id;
{{constructorAssignments}}
        }

        public function getId(): int {
            return $this->id;
        }

{{getters}}
    }
?>

