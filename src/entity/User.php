<?php
   class User{
      public function __construct(
	 private int $cin,
	 private string $nom,
	 private string $prenom,
	 private string $email,
	 private ?int $id=null
      ){}

      /*    public function __construct(private string $designation, */
      /*    private string $description, */
      /*    private ?int $id */
      /* ) {} */
      public function setId($id){
	 $this->id=$id;
      }
      public function getId():?int{
	 return $this->id;
      }
      public function __toString(): string {
        return sprintf(
            "User [ID: %s] | CIN: %d | Name: %s %s | Email: %s",
            $this->id ?? 'NULL',
            $this->cin,
            $this->nom,
            $this->prenom,
            $this->email
        );
      }
   }
   /* $a = new Etudiant("talel",new DateTimeImmutable("2024-12-2"),"tabssi",1,1); */
   /* echo $a->toHtml("admin"); */
   /* $aymen = new User(12345678, "Aymen", "Ben Salah", "aymen@example.com", 1); */

   // Debugging made easy:
   /* echo $aymen;  */
   // Outputs: User [ID: 1] | CIN: 12345678 | Name: Aymen Ben Salah | Email: aymen@example.com

   // In your HTML table:
   /* echo $aymen->toHtml("admin"); */
?>


