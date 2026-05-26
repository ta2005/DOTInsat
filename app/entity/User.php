<?php
   class User{
      public function __construct(
	 private int $cin,
	 private string $nom,
	 private string $prenom,
	 private string $email,
	 private ?int $id=null
      ){}

    
      public function setId($id){
	 $this->id=$id;
      }
      public function getId():?int{
	 return $this->id;
      }
      public function getNom(){
	 return $this->nom;
      }
      public function getEmail(){
	 return $this->email;
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
     public function toHtml(string $role): string {
	// Prepare the action buttons only for admins
	$actions = "";
	if ($role === 'admin') {
	   $actions = "
	   <div class='action-group' style='display: flex; gap: 5px;'>
	      <form action='edit_user.php' method='POST' style='margin:0;'>
		 <input type='hidden' name='id' value='{$this->id}'>
		 <button class='btn-edit'>Edit</button>
	      </form>
	      <form action='delete_user.php' method='POST' style='margin:0;' onsubmit=\"return confirm('Supprimer cet utilisateur ?');\">
		 <input type='hidden' name='id' value='{$this->id}'>
		 <button class='btn-delete'>Delete</button>
	      </form>
	   </div>";
	}

	return sprintf(
	   "<tr>
	      <td>%d</td>
	      <td>%d</td>
	      <td>%s</td>
	      <td>%s</td>
	      <td>%s</td>
	      <td>%s</td>
	   </tr>",
	   $this->id,
	   $this->cin,
	   htmlspecialchars($this->nom),
	   htmlspecialchars($this->prenom),
	   htmlspecialchars($this->email),
	   $actions
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


