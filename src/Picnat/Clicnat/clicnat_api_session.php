<?php
namespace Picnat\Clicnat;

class clicnat_api_session extends bobs_element {
	protected $id_session;
	protected $id_utilisateur;
	protected $date;
	protected $valid;

	public function __construct($db, $id_session) {
		parent::__construct($db, 'sessions_api', 'id_session', $id_session);
	}

	const nb_essai_max = 5;
	const sql_count = "select count(*) as n from sessions_api where id_session=$1";

	public static function init($db, $utilisateur) {
		$id_session = false;
		for ($i=0; $i<self::nb_essai_max; $i++) {
			$id_session_test = bin2hex(openssl_random_pseudo_bytes(64));
			$q = bobs_qm()->query($db, "api_session_cpte", self::sql_count, [$id_session_test]);
			$r = self::fetch($q);
			if ($r['n'] == 0) {
				$id_session = $id_session_test;
				break;
			}
			bobs_log("clicnat_session::init essai $i");
		}

		if (!$id_session) return false;

		$data = [
			"id_utilisateur" => $utilisateur->id_utilisateur,
			"id_session" => $id_session,
			"date" => strftime("%Y-%m-%d %H:%M:%S", mktime())
		];

		self::insert($db, 'sessions_api', $data);
		return $id_session;
	}

	public function check() {
		// TODO vérifier date et valid
		return true;
	}

	public function utilisateur() {
		return get_utilisateur($this->db, $this->id_utilisateur);
	}
}
?>
