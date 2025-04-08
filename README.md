
## Detailed Workflow

1. **Form Submission**  
   The system receives data through a form via GET or POST methods.

2. **Data Collection & Metadata**  
   The submitted form data is captured. Additionally, the following metadata is appended:
   - HTTP referrer
   - Visitor IP address
   - User Agent string
   - Timestamp in a human-readable format

3. **Spam Filtering & Email Validation**  
   The system applies two key checks:  
   - **Spam Filter:** It scans all submitted values against a list of spam keywords loaded from `spamfilter.txt`.  
   - **Email Validation:** For fields that are valid email addresses, it validates the email by checking the domain and TLD against the IANA TLD list. If an email is missing a proper domain or uses an invalid TLD, the submission is flagged as spam.

4. **Storage of Submission Data**  
   - If the submission fails the spam filter or email validation, the JSON data is stored in the `mail/spam` folder.
   - If the submission passes both checks, the data is stored in the `mail/incoming` folder.

5. **Email Sending Condition**  
   The system then checks the HTTP referrer. Only if the referrer contains `"jocarsa.com"` and the submission is not marked as spam, it proceeds to send the email.

6. **SMTP Email Sending Process**  
   - Establishes a connection to the SMTP server over SSL.
   - Authenticates using `AUTH LOGIN` with credentials from `config.php`.
   - Sends the email headers (including subject and MIME headers) and the HTML-formatted message.
   - Closes the SMTP connection upon completion.

7. **User Redirection**  
   After processing the submission (regardless of whether an email was sent), the user is shown a success message and redirected to the main domain.

## Updated Flow Chart

Below is the updated flow chart diagram (using Mermaid syntax) that represents the complete conditional logic for processing a form submission:

```mermaid
flowchart TD
    A[Start: Form Submission]
    B[Capture Form Data (GET/POST)]
    C[Append Metadata: Referrer, IP, User Agent, Timestamp]
    D[Apply Spam Filter & Email Validation]
    D --> |Spam Detected or Email Invalid| E[Mark as Spam]
    D --> |No Spam & Valid Email| F[Proceed with Valid Data]
    E --> G[Save JSON to mail/spam]
    F --> H[Save JSON to mail/incoming]
    H --> I[Check HTTP_REFERER for valid domain (contains "jocarsa.com")]
    I --> |Invalid Referrer| J[Do Not Send Email]
    I --> |Valid Referrer| K[Connect to SMTP Server via SSL]
    K --> L[Authenticate with AUTH LOGIN]
    L --> M[Send Email Headers and Body]
    M --> N[Close SMTP Connection]
    J & N --> O[Display Success Message & Redirect]

