<?php
require_once __DIR__ . '/educational_catalog.php';

function gamehub_sync_catalog(PDO $pdo): void
{
    $pdo->exec("UPDATE games SET active=FALSE WHERE slug IN ('flappy-bird','super-mario')");
    $catalog = gamehub_educational_catalog();
    $covers = [
        'joao-bird' => '/games/JoaoBird/bg.png',
    ];

    $sql='INSERT INTO games(slug,title,description,cover_url,game_url,active)
          VALUES(:slug,:title,:description,:cover,:url,TRUE)
          ON CONFLICT(slug) DO UPDATE SET title=EXCLUDED.title,description=EXCLUDED.description,
          cover_url=EXCLUDED.cover_url,game_url=EXCLUDED.game_url,active=TRUE';
    $stmt=$pdo->prepare($sql);

    foreach($catalog as $slug=>$game){
        $stmt->execute([
            ':slug'=>$slug,
            ':title'=>$game['title'],
            ':description'=>$game['mission'],
            ':cover'=>$covers[$slug] ?? null,
            ':url'=>'/play.php?game='.urlencode($slug),
        ]);
    }
}
