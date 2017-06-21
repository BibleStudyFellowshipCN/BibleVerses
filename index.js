
var sqlite3 = require('sqlite3').verbose();
var db = new sqlite3.Database('bible.db');

var express = require('express')
var app = express()

function getVerseRange(verse) {
  const bookPos = verse.indexOf('/');
  const book = verse.substring(0, bookPos);
  verse = verse.substring(bookPos + 1);

  const rangePos = verse.indexOf('-');
  if (rangePos != -1) {
    verseStart = verse.substring(0, rangePos);
    verseEnd = verse.substring(rangePos + 1);
  } else {
    verseStart = verse;
    verseEnd = null;
  }

  result = verseStart.split(':');
  if (result.length == 2) {
    chapter1 = parseInt(result[0]);
    verse1 = parseInt(result[1]);
  } else {
    return null;
  }

  startIndex = book * 1000000 + chapter1 * 1000 + verse1;

  if (verseEnd == null) {
    chapter2 = chapter1;
    verse2 = verse1;
  } else {
    result = verseEnd.split(':');
    if (result.length == 2) {
      chapter2 = parseInt(result[0]);
      verse2 = parseInt(result[1]);
    } else if (result.length == 1) {
      chapter2 = chapter1;
      verse2 = parseInt(result[0]);
    } else {
      return null;
    }
  }

  endIndex = book * 1000000 + chapter2 * 1000 + verse2;

  return { start: startIndex, end: endIndex };
}

// GET method route
app.get('/verse/*', function (req, res) {
  const startTime = new Date();
  console.log(JSON.stringify({ path: req.path, ip: req.ip }));
  const query = req.path.substring('/verse/'.length);
  verseRange = getVerseRange(query);
  if (verseRange == null) {
    res.send(JSON.stringify({ Error: "Invalid input" }));
  } else {
    let result = {
      paragraphs: []
    };

    let resultChapter = {
      id: parseInt(verseRange.start / 1000 % 1000),
      title: '',
      verses: []
    }

    language = 'chs';
    if (req.headers.language) {
      const lang = req.headers.language.toLowerCase();
      if (lang == 'cht' || lang == 'eng') {
        language = lang;
      }
    }

    db.serialize(function () {
      const sql = "SELECT * FROM " + language + " WHERE id>=" + verseRange.start + " AND id<=" + verseRange.end;
      db.each(sql, function (err, row) {
        const chapter = parseInt(row.id / 1000 % 1000);
        const text = row.text.replace(/\n/g, '');
        if (chapter == resultChapter.id) {
          resultChapter.verses.push({
            verse: chapter + ":" + row.id % 1000,
            text
          });
        } else {
          result.paragraphs.push(resultChapter);
          resultChapter = {
            id: chapter,
            title: '',
            verses: [{
              verse: chapter + ":" + row.id % 1000,
              text
            }]
          }
        }
      }, function () {
        if (resultChapter.verses.length > 0) {
          result.paragraphs.push(resultChapter);
        }
        res.setHeader('content-type', 'application/json');
        res.send(JSON.stringify(result));
        console.log(JSON.stringify({ language, verse: verseRange, time: (new Date()) - startTime }));
      });
    });
  }
})

app.listen(3000)
