<?php
	namespace Database\Seeders;
	use Illuminate\Database\Seeder;
	use App\Models\User;
	class UserSeeder extends Seeder
	{
		/**
		 * Run the database seeds.
		 *
		 * @return void
		 */
		public function run()
		{

			$user = [

					'name'=>'admin',
					'email'=>'filleaoffice@gmail.com',
					'password'=> bcrypt('JCsnc2000'),

				];
			User::create($user);
		}
	}
?>
