import java.io.File;
import java.io.FileInputStream;
import java.io.FileNotFoundException;
import java.io.IOException;
import java.io.PrintWriter;

import org.apache.tika.exception.TikaException;
import org.apache.tika.metadata.Metadata;
import org.apache.tika.parser.ParseContext;
import org.apache.tika.parser.html.HtmlParser;
import org.apache.tika.sax.BodyContentHandler;
import org.xml.sax.SAXException;

public class ExtractContent {

	public static void main(String[] args) throws IOException,SAXException, TikaException {
		// TODO Auto-generated method stub
		PrintWriter writer = new PrintWriter ("/Users/yuerancai/Downloads/hw5/big.txt");
		String dirPath = "/Users/yuerancai/Downloads/NBC_News/HTML_files";
		File dir = new File(dirPath);
		try {

			for(File file: dir.listFiles()){
				//detecting the file type
			      BodyContentHandler handler = new BodyContentHandler(-1);
			      Metadata metadata = new Metadata();
			      FileInputStream inputstream = new FileInputStream(file);
			      ParseContext pcontext = new ParseContext();
			      
			      //Html parser 
			      HtmlParser htmlparser = new HtmlParser();
			      htmlparser.parse(inputstream, handler, metadata,pcontext);

			      //Content processing
			      String content = handler.toString().trim();
			      String replace = content.replaceAll(" +", " ").replaceAll("[\r\n]+", "\n");
			      
			      writer.print(replace);
//				  for(String ss: words){
//					  writer.print(ss+" ");
//				  }
			      

			}

		} catch (Exception e) {



		System.err.println("Caught IOException: " + e.getMessage());



		e.printStackTrace();

		}

		writer.close();

	}

}
